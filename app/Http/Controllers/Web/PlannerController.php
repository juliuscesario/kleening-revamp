<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\Staff;
use App\Models\StaffOffDay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlannerController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array(strtolower(trim($user->role)), ['admin', 'owner'])) {
            return redirect()->route('dashboard');
        }

        $date = $request->input('date', now()->toDateString());
        $areaId = $request->input('area_id');
        $viewMode = $request->input('view', 'list'); // 'staff' or 'list' as default
        $carbonDate = Carbon::parse($date);

        $areas = Area::orderBy('name')->get();

        // Build service orders query for the selected date
        $soQuery = ServiceOrder::with([
            'customer' => fn($q) => $q->withTrashed(),
            'address' => fn($q) => $q->withTrashed()->with('area'),
            'items.service.category',
            'staff',
            'invoice',
            'creator',
        ])
            ->whereDate('work_date', $date)
            ->whereIn('status', [
                ServiceOrder::STATUS_BOOKED,
                ServiceOrder::STATUS_PROSES,
                ServiceOrder::STATUS_DONE,
                ServiceOrder::STATUS_INVOICED,
                ServiceOrder::STATUS_CANCELLED,
            ]);

        // Area filter
        if ($areaId) {
            $soQuery->whereHas('address', fn($q) => $q->where('area_id', $areaId));
        }

        $serviceOrders = $soQuery->get();

        // For list view: sort by staff name (first staff) then by work_time
        $serviceOrders = $serviceOrders->sortBy([
            function ($a, $b) {
                $nameA = $a->staff->first()?->name ?? '';
                $nameB = $b->staff->first()?->name ?? '';
                $cmp = strcmp($nameA, $nameB);
                if ($cmp !== 0) return $cmp;
                // Same staff (or both unassigned): sort by work_time
                $timeA = $a->work_time ?? '23:59:59';
                $timeB = $b->work_time ?? '23:59:59';
                return strcmp($timeA, $timeB);
            },
        ])->values();

        // Compute lifecycle status for each SO
        $serviceOrders->each(function ($so) {
            $so->lifecycle_status = $this->computeLifecycleStatus($so);
        });

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

        // Separate unassigned jobs
        $unassignedJobs = $serviceOrders->filter(fn($so) => $so->staff->isEmpty() && $so->status !== ServiceOrder::STATUS_CANCELLED);
        $assignedJobs = $serviceOrders->filter(fn($so) => $so->staff->isNotEmpty());
        $cancelledJobs = $serviceOrders->filter(fn($so) => $so->status === ServiceOrder::STATUS_CANCELLED);

        // Group by primary staff (first assigned staff) for staff view
        $jobsByStaff = collect();
        foreach ($assignedJobs as $so) {
            foreach ($so->staff as $staffMember) {
                if (!$jobsByStaff->has($staffMember->id)) {
                    $jobsByStaff[$staffMember->id] = collect([
                        'staff' => $staffMember,
                        'jobs' => collect(),
                    ]);
                }
                // Only add if not already added (avoid duplicates when multiple staff on same job)
                if (!$jobsByStaff[$staffMember->id]['jobs']->contains('id', $so->id)) {
                    $jobsByStaff[$staffMember->id]['jobs']->push($so);
                }
            }
        }

        // Sort each staff's jobs by time and build route summary
        $jobsByStaff = $jobsByStaff->map(function ($group) {
            $group['jobs'] = $group['jobs']->sortBy(function ($so) {
                return $so->work_time ?? '23:59:59';
            })->values();

            // Build route summary
            $route = $group['jobs']->map(function ($so) {
                $lokasi = $so->address?->lokasi ?? '—';
                $time = $so->work_time ? Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '—';
                return $lokasi . ' (' . $time . ')';
            })->implode(' → ');
            $group['route'] = $route;

            return $group;
        })->sortBy(fn($g) => $g['staff']->name);

        // Get all services for quick booking
        $allServices = Service::with('category')->orderBy('name')->get();

        // Service categories for badge display
        $serviceCategories = \App\Models\ServiceCategory::all()->keyBy('id');

        // Navigation dates
        $prevDate = $carbonDate->copy()->subDay()->toDateString();
        $nextDate = $carbonDate->copy()->addDay()->toDateString();
        $today = now()->toDateString();

        // Summary counts
        $totalJobs = $serviceOrders->where('status', '!=', ServiceOrder::STATUS_CANCELLED)->count();
        
        $totalStaffCount = $allStaff->count();
        $offStaffCount = $offDays->count();
        $assignedStaffIds = $assignedJobs->flatMap(fn($so) => $so->staff->pluck('id'))->unique();
        $assignedStaffCount = $assignedStaffIds->count();
        $availableStaffCount = max(0, $totalStaffCount - $offStaffCount - $assignedStaffCount);

        return view('pages.planner.index', compact(
            'date',
            'carbonDate',
            'areaId',
            'viewMode',
            'areas',
            'serviceOrders',
            'unassignedJobs',
            'assignedJobs',
            'cancelledJobs',
            'jobsByStaff',
            'allStaff',
            'offDays',
            'allServices',
            'serviceCategories',
            'prevDate',
            'nextDate',
            'today',
            'totalJobs',
            'totalStaffCount',
            'offStaffCount',
            'availableStaffCount',
            'assignedStaffCount',
            'assignedStaffIds',
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
     * AJAX: Update a service order field inline.
     */
    public function updateField(Request $request, ServiceOrder $serviceOrder)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $allowedFields = ['work_notes', 'staff_notes', 'work_time'];

        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Field not allowed.'], 400);
        }

        if ($field === 'work_time' && $value) {
            $value = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
        }

        $serviceOrder->$field = $value;
        $serviceOrder->save();

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Update staff assignment for a service order.
     */
    public function updateStaff(Request $request, ServiceOrder $serviceOrder)
    {
        $request->validate([
            'staff' => 'required|array',
            'staff.*' => 'exists:staff,id',
        ]);

        $serviceOrder->staff()->sync($request->staff);

        // Reload and return updated staff list
        $serviceOrder->load('staff');
        $staffNames = $serviceOrder->staff->pluck('name')->implode(', ');

        return response()->json([
            'success' => true,
            'staff_names' => $staffNames,
            'staff_ids' => $serviceOrder->staff->pluck('id'),
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

        $serviceOrder = DB::transaction(function () use ($request, $user) {
            $so = ServiceOrder::create([
                'customer_id' => $request->customer_id,
                'address_id' => $request->address_id,
                'work_date' => $request->work_date,
                'work_time' => Carbon::createFromFormat('H:i', $request->work_time)->format('H:i:s'),
                'work_notes' => $request->work_notes,
                'status' => 'booked',
                'created_by' => $user->id,
                'so_number' => 'SO-' . date('Ymd') . '-' . str_pad(ServiceOrder::withoutGlobalScopes()->count() + 1, 4, '0', STR_PAD_LEFT),
            ]);

            $serviceIds = array_column($request->services, 'service_id');
            $services = Service::whereIn('id', $serviceIds)->get()->keyBy('id');

            foreach ($request->services as $serviceData) {
                $service = $services->get($serviceData['service_id']);
                if ($service) {
                    ServiceOrderItem::create([
                        'service_order_id' => $so->id,
                        'service_id' => $service->id,
                        'price' => $service->price,
                        'quantity' => $serviceData['quantity'],
                        'total' => $service->price * $serviceData['quantity'],
                    ]);
                }
            }

            if ($request->has('staff') && !empty($request->staff)) {
                $so->staff()->attach($request->staff);
            }

            return $so;
        });

        return response()->json([
            'success' => true,
            'message' => 'Service Order ' . $serviceOrder->so_number . ' berhasil dibuat.',
            'redirect' => route('web.planner.index', ['date' => $request->work_date]),
        ]);
    }
}
