<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Staff;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServiceOrderController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        return view('pages.service-orders.index');
    }

    public function create(Request $request)
    {
        return view('pages.service-orders.create');
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $user = Auth::user();

        // Apply the policy
        $this->authorize('view', $serviceOrder);

        // Authorization for co_owner (existing logic)
        if ($user->role === 'co_owner') {
            // Load address with trashed to check area_id even if address is soft-deleted
            $serviceOrder->load(['address' => function ($query) {
                $query->withTrashed()->with('area'); // Ensure area is loaded
            }]);

            if ($serviceOrder->address && $user->area_id !== $serviceOrder->address->area->id) { // Changed $serviceOrder->address->area_id to $serviceOrder->address->area->id
                abort(403, 'Anda tidak diizinkan melihat pesanan di luar area Anda.');
            }
        }

        $relationsToLoad = ['customer' => function ($query) {
            $query->withTrashed();
        }, 'address' => function ($query) {
            $query->withTrashed();
        }, 'address.area', 'items.service', 'staff'];

        $isStaff = ($user->role === 'staff');

        if ($isStaff) {
            $relationsToLoad[] = 'creator'; // Load creator if user is staff
        } else {
            $relationsToLoad[] = 'workPhotos.uploader'; // Load work photos and their uploaders for non-staff
        }

        $serviceOrder->load($relationsToLoad);

        $allServices = Service::all();
        $allStaff = Staff::all();

        if ($isStaff) {
            return view('pages.service-orders.staff-show', compact('serviceOrder', 'isStaff'));
        } else {
            return view('pages.service-orders.show', compact('serviceOrder', 'allServices', 'allStaff', 'isStaff'));
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'address_id' => 'required|exists:addresses,id',
            'work_date' => 'required|date',
            'services' => 'required|array|min:1',
            'services.*' => 'required|exists:services,id',
            'staff' => 'nullable|array',
            'staff.*' => 'exists:staff,id',
        ]);

        $user = Auth::user();

        // Authorization check again, just in case
        $address = Address::findOrFail($request->address_id);
        if ($user->role === 'co_owner' && $user->area_id !== $address->area_id) {
            abort(403, 'Anda tidak diizinkan membuat pesanan untuk area ini.');
        }

        $serviceOrder = DB::transaction(function () use ($request, $user) {
            $so = ServiceOrder::create([
                'customer_id' => $request->customer_id,
                'address_id' => $request->address_id,
                'work_date' => $request->work_date,
                'work_notes' => $request->work_notes,
                'staff_notes' => $request->staff_notes,
                'status' => 'booked',
                'created_by' => $user->id,
                // This SO number generation can have race conditions. Consider a more robust method.
                'so_number' => 'SO-' . date('Ymd') . '-' . str_pad(ServiceOrder::count() + 1, 4, '0', STR_PAD_LEFT),
            ]);

            $totalPrice = 0;
            foreach ($request->services as $serviceId) {
                $service = Service::find($serviceId);
                ServiceOrderItem::create([
                    'service_order_id' => $so->id,
                    'service_id' => $service->id,
                    'price' => $service->price,
                ]);
                $totalPrice += $service->price;
            }

            if ($request->has('staff')) {
                $so->staff()->attach($request->staff);
            }

            return $so;
        });

        return redirect()->route('web.service-orders.show', $serviceOrder)->with('success', 'Service Order berhasil dibuat.');
    }

    public function printPdf(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load([
            'customer' => function ($query) {
                $query->withTrashed();
            },
            'address' => function ($query) {
                $query->withTrashed();
            },
            'address.area',
            'items.service',
            'staff' => function ($query) {
                $query->withPivot('signature_image'); // Load staff signatures
            },
            'workPhotos.uploader' // Load work photos and their uploaders
        ]);

        $pdf = Pdf::loadView('pdf.service-order', compact('serviceOrder'));
        return $pdf->download('service-order-' . $serviceOrder->so_number . '.pdf');
    }

    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        $originalStatus = $serviceOrder->status;
        $newStatus = $request->status;
        $user = Auth::user();

        $rules = [
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'status' => 'required|in:booked,proses,done,cancelled,invoiced',
            'services' => 'sometimes|array|min:1',
            'services.*.service_id' => 'sometimes|exists:services,id',
            'services.*.quantity' => 'sometimes|integer|min:1',
            'staff' => 'nullable|array',
            'staff.*' => 'exists:staff,id',
        ];

        $request->validate($rules);

        // --- Status Transition Logic ---
        if ($originalStatus !== $newStatus) {
            $transition = $serviceOrder->canTransitionTo($newStatus, $user, $request->owner_password);

            if (!$transition['allowed']) {
                return response()->json(['success' => false, 'message' => $transition['message']], 400);
            }
        }

        DB::transaction(function () use ($request, $serviceOrder, $newStatus) {
            // Update notes and status
            $serviceOrder->work_notes = $request->work_notes;
            $serviceOrder->staff_notes = $request->staff_notes;
            $serviceOrder->status = $newStatus;
            $serviceOrder->save();

            // Sync services
            if ($request->has('services')) {
                $currentServiceItemIds = $serviceOrder->items->pluck('id')->toArray();
                $newServiceItemIds = [];

                foreach ($request->services as $serviceData) {
                    $service = Service::find($serviceData['service_id']);
                    $quantity = $serviceData['quantity'];
                    $price = $service->price;
                    $total = $price * $quantity;

                    // Check if it's an existing item or a new one
                    if (isset($serviceData['id']) && in_array($serviceData['id'], $currentServiceItemIds)) {
                        // Update existing item
                        $item = ServiceOrderItem::find($serviceData['id']);
                        if ($item) {
                            $item->service_id = $service->id;
                            $item->quantity = $quantity;
                            $item->price = $price;
                            $item->total = $total;
                            $item->save();
                        }
                        $newServiceItemIds[] = $serviceData['id'];
                    } else {
                        // Create new item
                        $newItem = ServiceOrderItem::create([
                            'service_order_id' => $serviceOrder->id,
                            'service_id' => $service->id,
                            'quantity' => $quantity,
                            'price' => $price,
                            'total' => $total,
                        ]);
                        // Add the ID of the newly created item to prevent it from being deleted
                        $newServiceItemIds[] = $newItem->id;
                    }
                }

                // Delete removed service items
                ServiceOrderItem::where('service_order_id', $serviceOrder->id)
                                 ->whereNotIn('id', $newServiceItemIds)
                                 ->delete();
            }

            // Sync staff
            if ($request->has('staff')) {
                $serviceOrder->staff()->sync($request->staff);
            }
        });

        return response()->json(['success' => true, 'message' => 'Service Order updated successfully.']);
    }
}