<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Address;
use App\Models\Invoice;
use App\Models\MachineAttendance;
use App\Models\OrderSession;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\Staff;
use App\Models\StaffOffDay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlannerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!in_array(strtolower(trim($user->role)), ['admin', 'owner'])) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'area_id' => 'nullable|integer|exists:areas,id',
            'view' => 'nullable|in:staff,list',
        ]);

        $date = $request->input('date', now()->toDateString());
        $areaId = $request->input('area_id');
        $viewMode = $request->input('view', 'list'); // 'staff' or 'list' as default
        $carbonDate = Carbon::parse($date);

        $areas = Area::orderBy('name')->get();

        // Build sessions query for the selected date
        $sessionsQuery = OrderSession::withoutGlobalScopes()
            ->with([
                'serviceOrder' => function ($q) {
                    $q->withoutGlobalScopes()
                        ->with([
                            'customer' => fn($cq) => $cq->withTrashed(),
                            'address' => fn($aq) => $aq->withTrashed()->with('area'),
                            'items.service.category',
                            'invoice',
                            'creator',
                        ]);
                },
                'staff',
            ])
            ->whereDate('tanggal', $date)
            ->whereIn('status', ['booked', 'proses', 'done']);

        // Area filter — filter by parent order's address area
        if ($areaId) {
            $sessionsQuery->whereHas('serviceOrder.address', fn($q) => $q->where('area_id', $areaId));
        }

        $sessions = $sessionsQuery->get();

        // Compute lifecycle status on each session's parent order
        $sessions->each(function ($session) {
            $session->lifecycle_status = $this->computeLifecycleStatus($session->serviceOrder);
        });

        // Sort sessions: by staff name first, then by jam (time)
        $sessions = $sessions->sortBy([
            function ($a, $b) {
                $nameA = $a->staff->first()?->name ?? '';
                $nameB = $b->staff->first()?->name ?? '';
                $cmp = strcmp($nameA, $nameB);
                if ($cmp !== 0) return $cmp;
                $timeA = $a->jam ?? '23:59:59';
                $timeB = $b->jam ?? '23:59:59';
                return strcmp($timeA, $timeB);
            },
        ])->values();

        // Get all active staff (strictly role 'staff' only)
        $staffQuery = Staff::where('is_active', true)
            ->whereHas('user', function($q) {
                $q->where('role', 'staff');
            })
            ->orderBy('name');
        if ($areaId) {
            $staffQuery->where('area_id', $areaId);
        }
        $allStaff = $staffQuery->get();

        // Get staff off days for this date
        $offDays = StaffOffDay::whereDate('off_date', $date)
            ->with('staff')
            ->get()
            ->keyBy('staff_id');

        // Separate unassigned and assigned sessions
        $unassignedSessions = $sessions->filter(fn($s) => $s->staff->isEmpty());
        $assignedSessions = $sessions->filter(fn($s) => $s->staff->isNotEmpty());

        // Cancelled sessions (parent order is cancelled)
        $cancelledSessions = OrderSession::withoutGlobalScopes()
            ->with(['serviceOrder.customer', 'staff'])
            ->whereDate('tanggal', $date)
            ->whereHas('serviceOrder', function ($q) {
                $q->withoutGlobalScopes()->where('status', ServiceOrder::STATUS_CANCELLED);
            })
            ->get();

        // Group by staff for staff view
        $sessionsByStaff = collect();
        foreach ($assignedSessions as $session) {
            foreach ($session->staff as $staffMember) {
                if (!$sessionsByStaff->has($staffMember->id)) {
                    $sessionsByStaff[$staffMember->id] = collect([
                        'staff' => $staffMember,
                        'sessions' => collect(),
                    ]);
                }
                if (!$sessionsByStaff[$staffMember->id]['sessions']->contains('id', $session->id)) {
                    $sessionsByStaff[$staffMember->id]['sessions']->push($session);
                }
            }
        }

        // Sort each staff's sessions by time and build route summary
        $sessionsByStaff = $sessionsByStaff->map(function ($group) {
            $group['sessions'] = $group['sessions']->sortBy(function ($session) {
                return $session->jam ?? '23:59:59';
            })->values();

            // Build route summary
            $route = $group['sessions']->map(function ($session) {
                $so = $session->serviceOrder;
                $lokasi = $so->address?->lokasi ?? '—';
                $time = $session->jam ? Carbon::parse($session->jam)->format('H:i') : '—';
                return $lokasi . ' (' . $time . ')';
            })->implode(' → ');
            $group['route'] = $route;

            return $group;
        })->sortBy(fn($g) => $g['staff']->name);

        // Get all services for quick booking
        $allServices = Service::with('category')->orderBy('name')->get();

        // Navigation dates
        $prevDate = $carbonDate->copy()->subDay()->toDateString();
        $nextDate = $carbonDate->copy()->addDay()->toDateString();
        $today = now()->toDateString();

        // Summary counts
        $totalSessions = $sessions->count();

        $totalStaffCount = $allStaff->count();
        $offStaffCount = $offDays->count();

        // Assigned count: count sessions per staff (not orders)
        $assignedStaffIds = $assignedSessions->flatMap(fn($s) => $s->staff->pluck('id'))->unique();
        $assignedStaffCount = $assignedStaffIds->count();
        $availableStaffCount = max(0, $totalStaffCount - $offStaffCount - $assignedStaffCount);

        // Active machine attendances today (pergi done, pulang not done)
        $activeAttendances = MachineAttendance::whereDate('date', $date)
            ->whereNotNull('photo_pergi_at')
            ->whereNull('photo_pulang_at')
            ->with('machines')
            ->get()
            ->mapWithKeys(fn($a) => [
                $a->staff_id => $a->machines->pluck('code')->join(', ')
            ]);

        return view('pages.planner.index', compact(
            'date',
            'carbonDate',
            'areaId',
            'viewMode',
            'areas',
            'sessions',
            'unassignedSessions',
            'assignedSessions',
            'cancelledSessions',
            'sessionsByStaff',
            'allStaff',
            'offDays',
            'allServices',
            'prevDate',
            'nextDate',
            'today',
            'totalSessions',
            'totalStaffCount',
            'offStaffCount',
            'availableStaffCount',
            'assignedStaffCount',
            'assignedStaffIds',
            'activeAttendances',
        ));
    }

    /**
     * Compute a combined lifecycle status from SO + Invoice.
     */
    private function computeLifecycleStatus(ServiceOrder $so): string
    {
        if ($so->status === ServiceOrder::STATUS_CANCELLED) {
            return 'cancelled';
        }
        if ($so->status === ServiceOrder::STATUS_BOOKED) {
            return 'booked';
        }
        if ($so->status === ServiceOrder::STATUS_PROSES) {
            return 'proses';
        }
        if ($so->status === ServiceOrder::STATUS_DONE) {
            return 'done';
        }

        // Status is 'invoiced' — check invoice status
        if ($so->invoice) {
            return match ($so->invoice->status) {
                Invoice::STATUS_NEW => 'invoiced',
                Invoice::STATUS_SENT => 'sent',
                Invoice::STATUS_OVERDUE => 'overdue',
                Invoice::STATUS_PAID => 'paid',
                Invoice::STATUS_CANCELLED => 'inv_cancelled',
                default => 'invoiced',
            };
        }

        return 'invoiced';
    }

    /**
     * AJAX: Update a session or its parent order field inline.
     */
    public function updateField(Request $request, OrderSession $orderSession)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        // Session-level fields
        $sessionFields = ['jam', 'notes'];
        // Order-level fields
        $orderFields = ['work_notes', 'staff_notes'];

        if (!in_array($field, array_merge($sessionFields, $orderFields))) {
            return response()->json(['success' => false, 'message' => 'Field not allowed.'], 400);
        }

        if (in_array($field, $sessionFields)) {
            // Update the session itself
            if ($field === 'jam') {
                if ($value) {
                    try {
                        $parsed = Carbon::createFromFormat('H:i', $value);
                        if (!$parsed) {
                            return response()->json(['success' => false, 'message' => 'Format waktu tidak valid.'], 422);
                        }
                        $value = $parsed->format('H:i:s');
                    } catch (\Exception $e) {
                        return response()->json(['success' => false, 'message' => 'Format waktu tidak valid.'], 422);
                    }
                } else {
                    $value = null;
                }
            }

            $orderSession->$field = $value;
            $orderSession->save();
        } else {
            // Update the parent service order
            $serviceOrder = $orderSession->serviceOrder;
            $serviceOrder->$field = $value;
            $serviceOrder->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Update staff assignment for a session.
     */
    public function updateStaff(Request $request, OrderSession $orderSession)
    {
        $request->validate([
            'staff' => 'required|array',
            'staff.*' => 'exists:staff,id',
        ]);

        $orderSession->staff()->sync($request->staff);

        // Reload and return updated staff list
        $orderSession->load('staff');
        $staffNames = $orderSession->staff->pluck('name')->implode(', ');

        return response()->json([
            'success' => true,
            'staff_names' => $staffNames,
            'staff_ids' => $orderSession->staff->pluck('id'),
        ]);
    }

    /**
     * AJAX: Update lokasi on the booking's address.
     */
    public function updateLokasi(Request $request, ServiceOrder $serviceOrder)
    {
        $request->validate([
            'lokasi' => 'nullable|string|max:100',
            'address_id' => 'required|exists:addresses,id',
        ]);

        $lokasi = $request->input('lokasi');

        // Trim whitespace; empty string → null
        if (is_string($lokasi)) {
            $lokasi = trim($lokasi);
            if ($lokasi === '') {
                $lokasi = null;
            }
        }

        $address = Address::withoutGlobalScopes()
            ->where('id', $request->input('address_id'))
            ->firstOrFail();

        $address->update(['lokasi' => $lokasi]);

        return response()->json([
            'success' => true,
            'lokasi' => $lokasi,
        ]);
    }

    /**
     * AJAX: Toggle staff off day.
     */
    public function toggleStaffOff(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'date' => 'required|date',
        ]);

        $existing = StaffOffDay::where('staff_id', $request->staff_id)
            ->whereDate('off_date', $request->date)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['success' => true, 'is_off' => false]);
        }

        StaffOffDay::create([
            'staff_id' => $request->staff_id,
            'off_date' => $request->date,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'is_off' => true]);
    }

    /**
     * AJAX: Quick store a new service order from the planner.
     */
    public function quickStore(Request $request)
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
            'work_notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $soData = [
            'customer_id' => $request->customer_id,
            'address_id' => $request->address_id,
            'work_date' => $request->work_date,
            'work_time' => $request->work_time,
            'work_notes' => $request->work_notes,
            'services' => $request->services,
        ];

        $serviceOrder = app(\App\Actions\CreateServiceOrderAction::class)->execute(
            $soData,
            $request->staff ?? [],
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Service Order ' . $serviceOrder->so_number . ' berhasil dibuat.',
            'redirect' => route('web.planner.index', ['date' => $request->work_date]),
        ]);
    }
}
