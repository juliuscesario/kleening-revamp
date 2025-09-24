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
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceOrderController extends Controller
{
    public function index()
    {
        return view('pages.service-orders.index');
    }

    public function create(Request $request)
    {
        $address = Address::with('customer')->findOrFail($request->address_id);
        $user = Auth::user();

        // Authorization
        if ($user->role === 'co_owner' && $user->area_id !== $address->area_id) {
            abort(403, 'Anda tidak diizinkan membuat pesanan untuk area ini.');
        }

        $services = Service::all();
        $staff = Staff::where('area_id', $address->area_id)->get();

        return view('pages.service-orders.create', compact('address', 'services', 'staff'));
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $user = Auth::user();

        // Authorization for co_owner
        if ($user->role === 'co_owner') {
            // Load address with trashed to check area_id even if address is soft-deleted
            $serviceOrder->load(['address' => function ($query) {
                $query->withTrashed();
            }]);

            if ($serviceOrder->address && $user->area_id !== $serviceOrder->address->area_id) {
                abort(403, 'Anda tidak diizinkan melihat pesanan di luar area Anda.');
            }
        }

        $serviceOrder->load(['customer' => function ($query) {
            $query->withTrashed();
        }, 'address' => function ($query) {
            $query->withTrashed();
        }, 'address.area', 'items.service', 'staff']);

        $allServices = Service::all();
        $allStaff = Staff::all();

        return view('pages.service-orders.show', compact('serviceOrder', 'allServices', 'allStaff'));
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
                'status' => 'baru',
                'created_by' => $user->id,
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

        // For now, redirect to the customer detail page
        return redirect()->route('web.customers.show', $request->customer_id)->with('success', 'Service Order berhasil dibuat.');
    }

    public function printPdf(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load(['customer' => function ($query) {
            $query->withTrashed();
        }, 'address' => function ($query) {
            $query->withTrashed();
        }, 'address.area', 'items.service', 'staff']);

        $pdf = Pdf::loadView('pdf.service-order', compact('serviceOrder'));
        return $pdf->download('service-order-' . $serviceOrder->so_number . '.pdf');
    }

    public function update(Request $request, ServiceOrder $serviceOrder)
    {
        $request->validate([
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'status' => 'required|in:baru,proses,selesai,batal',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'staff' => 'nullable|array',
            'staff.*' => 'exists:staff,id',
        ]);

        DB::transaction(function () use ($request, $serviceOrder) {
            // Update notes and status
            $serviceOrder->work_notes = $request->work_notes;
            $serviceOrder->staff_notes = $request->staff_notes;
            $serviceOrder->status = $request->status;
            $serviceOrder->save();

            // Sync services
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

            // Sync staff
            $serviceOrder->staff()->sync($request->staff);
        });

        return response()->json(['success' => true, 'message' => 'Service Order updated successfully.']);
    }
}
