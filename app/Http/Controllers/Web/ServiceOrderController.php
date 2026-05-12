<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Staff;
use App\Models\ServiceOrder;
use App\Models\Invoice;
use App\Models\MachineAttendance;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Address; // Add this line
use Carbon\Carbon;
use App\Actions\CreateServiceOrderAction;
use App\Actions\UpdateServiceOrderAction;

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
            $serviceOrder->load([
                'address' => function ($query) {
                    $query->withTrashed()->with('area'); // Ensure area is loaded
                }
            ]);

            if ($serviceOrder->address && $user->area_id !== $serviceOrder->address->area->id) { // Changed $serviceOrder->address->area_id to $serviceOrder->address->area->id
                abort(403, 'Anda tidak diizinkan melihat pesanan di luar area Anda.');
            }
        }

        $relationsToLoad = [
            'customer' => function ($query) {
                $query->withTrashed();
            },
            'address' => function ($query) {
                $query->withTrashed();
            },
            'address.area',
            'items.service',
            'sessions.staff',
            'invoice'
        ];

        $isStaff = ($user->role === 'staff');

        if ($isStaff) {
            $relationsToLoad[] = 'creator';
            $relationsToLoad[] = 'workPhotos';
            $relationsToLoad[] = 'finalOrder';
        } else {
            $relationsToLoad[] = 'workPhotos.uploader';
            $relationsToLoad[] = 'finalOrder';
        }

        $serviceOrder->load($relationsToLoad);

        $allServices = Service::all();
        $allStaff = Staff::whereHas('user', function ($q) {
            $q->where('role', 'staff');
        })->get();

        // Get assigned staff from session 1 of this order (source of truth)
        $session1 = $serviceOrder->sessions()
            ->orderBy('id', 'asc')
            ->with('staff')
            ->first();

        $selectedStaffIds = $session1
            ? $session1->staff->pluck('id')->toArray()
            : [];

        $workPhotos = $serviceOrder->workPhotos->keyBy('type');

        // SO Gate: check if staff has uploaded Mesin Pergi today
        $hasMesinPergi = true; // default true for non-staff
        if ($isStaff) {
            $staff = $user->staff;
            if ($staff) {
                $hasMesinPergi = MachineAttendance::hasActiveAttendanceToday($staff->id);
            }
        }

        if ($isStaff) {
            return view('pages.service-orders.staff-show', compact('serviceOrder', 'isStaff', 'hasMesinPergi'));
        } else {
            return view('pages.service-orders.show', compact('serviceOrder', 'allServices', 'allStaff', 'isStaff', 'workPhotos', 'selectedStaffIds'));
        }
    }

    public function store(Request $request, CreateServiceOrderAction $action)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'address_id' => 'required|exists:addresses,id',
            'work_date' => 'required|date',
            'work_time' => 'required|date_format:H:i',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|numeric|min:0.1',
            'staff' => 'nullable|array',
            'staff.*' => 'exists:staff,id',
        ]);

        $user = Auth::user();

        // Authorization check again, just in case
        $address = Address::findOrFail($request->address_id);
        if ($user->role === 'co_owner' && $user->area_id !== $address->area_id) {
            abort(403, 'Anda tidak diizinkan membuat pesanan untuk area ini.');
        }

        $hasPendingServiceOrder = ServiceOrder::where('customer_id', $request->customer_id)
            ->whereNotIn('status', [
                ServiceOrder::STATUS_DONE,
                'cancel',
                ServiceOrder::STATUS_INVOICED,
            ])
            ->exists();

        $hasOverdueInvoice = Invoice::whereHas('serviceOrder', function ($query) use ($request) {
            $query->where('customer_id', $request->customer_id);
        })
            ->whereNotIn('status', [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])
            ->whereDate('due_date', '<', now())
            ->exists();

        $blockingMessages = [];

        if ($hasPendingServiceOrder) {
            $blockingMessages[] = 'Customer masih memiliki Service Order yang harus diselesaikan terlebih dahulu.';
        }

        if ($hasOverdueInvoice) {
            $blockingMessages[] = 'Customer memiliki invoice tertunggak yang harus dibayar terlebih dahulu.';
        }

        if (!empty($blockingMessages)) {
            return redirect()
                ->back()
                ->withErrors(['customer_id' => $blockingMessages])
                ->withInput();
        }

        $soData = [
            'customer_id' => $request->customer_id,
            'address_id' => $request->address_id,
            'work_date' => $request->work_date,
            'work_time' => $request->work_time, // Action handles H:i → H:i:s conversion
            'work_notes' => $request->work_notes,
            'staff_notes' => $request->staff_notes,
            'services' => $request->input('services', []),
        ];

        $serviceOrder = $action->execute($soData, $request->input('staff', []), $user->id);

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
            'sessions.staff',
            'workPhotos.uploader' // Load work photos and their uploaders
        ]);

        $pdf = Pdf::loadView('pdf.service-order', compact('serviceOrder'));
        return $pdf->download('service-order-' . $serviceOrder->so_number . '.pdf');
    }

    public function update(Request $request, ServiceOrder $serviceOrder, UpdateServiceOrderAction $action)
    {
        // Load the invoice relationship to check its status
        $serviceOrder->load('invoice');

        // Check if the service order has an invoice and if it's paid
        if ($serviceOrder->invoice && $serviceOrder->invoice->status === \App\Models\Invoice::STATUS_PAID) {
            return response()->json(['success' => false, 'message' => 'Service Order cannot be edited because its invoice has been paid.'], 403);
        }

        $originalStatus = $serviceOrder->status;
        $newStatus = $request->status;
        $user = Auth::user();

        $rules = [
            'work_notes' => 'nullable|string',
            'staff_notes' => 'nullable|string',
            'status' => 'required|in:booked,proses,done,cancelled,invoiced',
            'work_date' => 'sometimes|required|date',
            'work_time' => 'sometimes|required|date_format:H:i',
            'services' => 'sometimes|array|min:1',
            'services.*.service_id' => 'sometimes|exists:services,id',
            'services.*.quantity' => 'sometimes|numeric|min:0.1',
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

        // --- Work Date Update Logic ---
        if ($request->has('work_date') && $request->work_date != $serviceOrder->work_date) {
            if (!in_array($user->role, ['admin', 'owner'])) {
                return response()->json(['success' => false, 'message' => 'Hanya Admin dan Owner yang dapat mengubah tanggal pengerjaan.'], 403);
            }
            if (!in_array($serviceOrder->status, ['booked', 'proses'])) {
                return response()->json(['success' => false, 'message' => 'Tanggal hanya dapat diubah saat status Booked atau Proses.'], 400);
            }
            if (Carbon::parse($request->work_date)->startOfDay()->lt(Carbon::now()->startOfDay())) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat mengubah tanggal ke masa lalu (backdate).'], 400);
            }
        }

        $soData = [
            'status' => $newStatus,
        ];
        if ($request->has('work_notes')) {
            $soData['work_notes'] = $request->work_notes;
        }
        if ($request->has('staff_notes')) {
            $soData['staff_notes'] = $request->staff_notes;
        }
        if ($request->has('work_date')) {
            $soData['work_date'] = $request->work_date;
        }
        if ($request->has('work_time')) {
            $soData['work_time'] = $request->work_time; // Action handles H:i → H:i:s conversion
        }
        if ($request->has('services')) {
            $soData['services'] = $request->input('services');
        }

        $action->execute(
            $serviceOrder,
            $soData,
            $request->has('staff') ? $request->input('staff') : null
        );

        return response()->json(['success' => true, 'message' => 'Service Order updated successfully.']);
    }

    public function unassigned()
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $unassignedServiceOrders = ServiceOrder::whereDoesntHave('sessions.staff')
            ->where('status', 'booked') // Assuming 'booked' is the status for unassigned jobs
            ->orderBy('work_date', 'asc')
            ->orderBy('work_time', 'asc')
            ->get();

        return view('pages.service-orders.index', ['serviceOrders' => $unassignedServiceOrders]);
    }

    public function updateStatus(Request $request, ServiceOrder $serviceOrder)
    {
        $newStatus = $request->input('status');
        $user = Auth::user();

        $transition = $serviceOrder->canTransitionTo($newStatus, $user, $request->owner_password);

        if (!$transition['allowed']) {
            return response()->json(['success' => false, 'message' => $transition['message']], 400);
        }

        $serviceOrder->status = $newStatus;
        if ($newStatus === ServiceOrder::STATUS_DONE) {
            $serviceOrder->work_proof_completed_at = now();
        }
        $serviceOrder->save();

        // Update customer's last_order_date when status changes
        $serviceOrder->load('customer');
        $serviceOrder->customer->syncLastOrderDate();

        return response()->json(['success' => true, 'message' => 'Status berhasil diubah ke ' . ucfirst($newStatus) . '.']);
    }

    public function markComplete(ServiceOrder $serviceOrder)
    {
        // Only allow if SO is NOT already done
        if ($serviceOrder->status === 'done') {
            return back()->with('error', 'Service order sudah berstatus done.');
        }

        $action = new \App\Actions\CompleteServiceOrderAction();
        $action->execute($serviceOrder);

        return back()->with('success', 'Service order dan semua sesi telah ditandai selesai.');
    }

    public function cancel(ServiceOrder $serviceOrder)
    {
        // Guard: only cancel if status is booked or proses
        if (!in_array($serviceOrder->status, ['booked', 'proses'])) {
            return back()->with('error', 'Order tidak dapat dibatalkan.');
        }

        // Cancel the SO
        $serviceOrder->update(['status' => 'cancel']);

        // Cancel all non-done sessions
        $serviceOrder->sessions()
            ->whereNotIn('status', ['done'])
            ->update(['status' => 'cancel']);

        return back()->with('success', 'Order berhasil dibatalkan.');
    }
}
