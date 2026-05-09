<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\OrderSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Area;
use App\Models\MachineAttendance;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with filtered service orders.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $viewData = [];
        $today = Carbon::today();
        $role = strtolower(trim($user->role));

        if ($role === 'admin') {
            return redirect()->route('web.planner.index');
        }

        if ($role === 'owner' || $role === 'co_owner') {
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();

            // Scoped queries
            $invoiceQuery = Invoice::query();
            $customerQuery = Customer::query();
            $serviceOrderQuery = ServiceOrder::query();

            if ($user->role === 'co_owner') {
                $areaId = $user->area_id;
                $invoiceQuery->whereHas('serviceOrder.address', fn($q) => $q->where('area_id', $areaId));
                $customerQuery->whereHas('addresses', fn($q) => $q->where('area_id', $areaId));
                $serviceOrderQuery->whereHas('address', fn($q) => $q->where('area_id', $areaId));
            }

            // KPIs
            $viewData['monthlyRevenue'] = (clone $invoiceQuery)->where('status', Invoice::STATUS_PAID)
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');

            $viewData['jobsCompletedThisMonth'] = (clone $invoiceQuery)->where('status', Invoice::STATUS_PAID)
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->count();

            $viewData['outstandingInvoices'] = (clone $invoiceQuery)->where('status', Invoice::STATUS_SENT)->sum('grand_total');
            $viewData['overdueInvoices'] = (clone $invoiceQuery)->where('status', Invoice::STATUS_OVERDUE)->sum('grand_total');
            $viewData['newCustomersThisMonth'] = (clone $customerQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            // Service Order Funnel
            $viewData['funnelBooked'] = (clone $serviceOrderQuery)->where('status', ServiceOrder::STATUS_BOOKED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $viewData['funnelProses'] = (clone $serviceOrderQuery)->where('status', ServiceOrder::STATUS_PROSES)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $viewData['funnelInvoiced'] = (clone $serviceOrderQuery)->where('status', ServiceOrder::STATUS_INVOICED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $viewData['funnelDone'] = (clone $serviceOrderQuery)->where('status', ServiceOrder::STATUS_DONE)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            // Monthly Revenue Chart (Daily)
            $viewData['dailyRevenue'] = (clone $invoiceQuery)->selectRaw('DATE(updated_at) as date, SUM(grand_total) as total')
                ->where('status', Invoice::STATUS_PAID)
                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // Area Performance (Owner only)
            if ($user->role === 'owner') {
                $areas = Area::all();

                $jobsCount = Invoice::join('service_orders', 'invoices.service_order_id', '=', 'service_orders.id')
                    ->join('addresses', 'service_orders.address_id', '=', 'addresses.id')
                    ->where('invoices.status', Invoice::STATUS_PAID)
                    ->whereBetween('invoices.updated_at', [$startOfMonth, $today])
                    ->selectRaw('addresses.area_id, count(invoices.id) as count')
                    ->groupBy('addresses.area_id')
                    ->pluck('count', 'area_id');

                $revenueSum = Invoice::join('service_orders', 'invoices.service_order_id', '=', 'service_orders.id')
                    ->join('addresses', 'service_orders.address_id', '=', 'addresses.id')
                    ->where('invoices.status', Invoice::STATUS_PAID)
                    ->whereBetween('invoices.updated_at', [$startOfMonth, $today])
                    ->selectRaw('addresses.area_id, sum(invoices.grand_total) as total')
                    ->groupBy('addresses.area_id')
                    ->pluck('total', 'area_id');

                $viewData['areaPerformance'] = $areas->map(function ($area) use ($jobsCount, $revenueSum) {
                    $area->jobs_completed_this_month_count = $jobsCount[$area->id] ?? 0;
                    $area->revenue_this_month_sum_grand_total = $revenueSum[$area->id] ?? 0;
                    return $area;
                });
            }

        } elseif ($role === 'admin') {
            // Admin Widgets
            $viewData['todaySchedule'] = ServiceOrder::whereDate('work_date', $today)
                ->whereIn('status', [ServiceOrder::STATUS_BOOKED, ServiceOrder::STATUS_PROSES])
                ->with('customer', 'staff')
                ->orderBy('work_date', 'desc')
                ->orderByRaw("COALESCE(work_time, '23:59:59') asc")
                ->get();

            $viewData['unassignedJobs'] = ServiceOrder::whereDoesntHave('staff')
                ->where('status', ServiceOrder::STATUS_BOOKED)
                ->get();

            $viewData['doneNotInvoiced'] = ServiceOrder::where('status', ServiceOrder::STATUS_DONE)
                ->whereDoesntHave('invoice')
                ->get();

            $viewData['tomorrowSchedule'] = ServiceOrder::whereDate('work_date', $today->copy()->addDay())
                ->whereIn('status', [ServiceOrder::STATUS_BOOKED, ServiceOrder::STATUS_PROSES])
                ->with('customer', 'staff')
                ->orderByRaw("COALESCE(work_time, '23:59:59') asc")
                ->get();

        } elseif ($role === 'staff') {
            $staffId = $user->staff->id;
            $tomorrow = Carbon::tomorrow();
            $startOfMonth = $today->copy()->subMonthNoOverflow()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();

            $sortBy = $request->input('sort_by', 'tanggal');
            $sortDir = $request->input('sort_dir', 'asc');

            if (!in_array($sortBy, ['tanggal', 'jam', 'created_at'])) {
                $sortBy = 'tanggal';
            }
            if (!in_array($sortDir, ['asc', 'desc'])) {
                $sortDir = 'asc';
            }

            $viewData['currentSortBy'] = $sortBy;
            $viewData['currentSortDir'] = $sortDir;

            $applySort = function ($query) use ($sortBy, $sortDir) {
                if ($sortBy === 'tanggal') {
                    $query->orderBy('tanggal', $sortDir)
                        ->orderByRaw("COALESCE(jam, '23:59:59') " . $sortDir);
                } elseif ($sortBy === 'jam') {
                    $query->orderByRaw("COALESCE(jam, '23:59:59') " . $sortDir)
                        ->orderBy('tanggal', $sortDir);
                } else {
                    $query->orderBy('created_at', $sortDir);
                }
            };

            $staffQueryBase = fn($q) => $q->withoutGlobalScopes()->where('staff.id', $staffId);

            $todaySessionsQuery = OrderSession::whereHas('staff', $staffQueryBase)
                ->whereDate('tanggal', $today)
                ->whereIn('status', ['booked', 'proses', 'done'])
                ->with([
                    'serviceOrder' => function ($q) {
                        $q->withoutGlobalScopes()
                            ->with(['customer', 'address', 'items.service.category']);
                    },
                ]);
            $applySort($todaySessionsQuery);
            $viewData['todaySessions'] = $todaySessionsQuery->get();

            $tomorrowSessionsQuery = OrderSession::whereHas('staff', $staffQueryBase)
                ->whereDate('tanggal', $tomorrow)
                ->where('status', 'booked')
                ->with([
                    'serviceOrder' => function ($q) {
                        $q->withoutGlobalScopes()
                            ->with(['customer', 'address', 'items.service.category']);
                    },
                ]);
            $applySort($tomorrowSessionsQuery);
            $viewData['tomorrowSessions'] = $tomorrowSessionsQuery->get();

            $pastSessionsQuery = OrderSession::whereHas('staff', $staffQueryBase)
                ->whereBetween('tanggal', [$today->copy()->subDays(6), $today->copy()->subDay()])
                ->whereIn('status', ['booked', 'proses', 'done'])
                ->with([
                    'serviceOrder' => function ($q) {
                        $q->withoutGlobalScopes()
                            ->with(['customer', 'address', 'items.service.category']);
                    },
                ]);
            $applySort($pastSessionsQuery);
            $viewData['pastSessions'] = $pastSessionsQuery->get();

            $viewData['cancelledSessions'] = OrderSession::whereHas('staff', $staffQueryBase)
                ->where('status', 'cancel')
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->with([
                    'serviceOrder' => function ($q) {
                        $q->withoutGlobalScopes()->with('customer');
                    },
                ])
                ->orderBy('tanggal', 'desc')
                ->orderByRaw("COALESCE(jam, '23:59:59') asc")
                ->get();

            $viewData['doneSessions'] = OrderSession::whereHas('staff', $staffQueryBase)
                ->where('status', 'done')
                ->where('tanggal', '>=', $today->copy()->subDays(10))
                ->with([
                    'serviceOrder' => function ($q) {
                        $q->withoutGlobalScopes()
                            ->with(['customer', 'address']);
                    },
                ])
                ->orderBy('tanggal', 'desc')
                ->orderByRaw("COALESCE(jam, '23:59:59') asc")
                ->get();

            $viewData['totalDoneCount'] = OrderSession::whereHas('staff', $staffQueryBase)
                ->where('status', 'done')->count();
            $viewData['todayDoneCount'] = OrderSession::whereHas('staff', $staffQueryBase)
                ->where('status', 'done')->whereDate('tanggal', $today)->count();
            $viewData['bookedCount'] = OrderSession::whereHas('staff', $staffQueryBase)
                ->where('status', 'booked')->whereDate('tanggal', '>=', $today)->count();

            // Machine attendance status for today
            $machineAttendance = MachineAttendance::where('staff_id', $staffId)
                ->whereDate('date', $today)
                ->with('machines.category')
                ->first();

            $machineAttendanceStatus = 'no_attendance';
            if ($machineAttendance) {
                if ($machineAttendance->photo_pergi_at && !$machineAttendance->photo_pulang_at) {
                    $machineAttendanceStatus = 'active';
                } elseif ($machineAttendance->photo_pergi_at && $machineAttendance->photo_pulang_at) {
                    $machineAttendanceStatus = 'completed';
                }
            }

            $viewData['machineAttendance'] = $machineAttendance;
            $viewData['machineAttendanceStatus'] = $machineAttendanceStatus;
        }

        // Default empty values for keys not set in every role
        $collectionKeys = [
            'dailyRevenue',
            'areaPerformance',
            'todaySchedule',
            'unassignedJobs',
            'tomorrowSchedule',
            'todaySessions',
            'tomorrowSessions',
            'pastSessions',
            'cancelledSessions',
            'doneSessions'
        ];
        $numericKeys = [
            'monthlyRevenue',
            'jobsCompletedThisMonth',
            'outstandingInvoices',
            'overdueInvoices',
            'newCustomersThisMonth',
            'funnelBooked',
            'funnelProses',
            'funnelInvoiced',
            'totalDoneCount',
            'todayDoneCount',
            'bookedCount'
        ];

        foreach ($collectionKeys as $key) {
            if (!isset($viewData[$key])) {
                $viewData[$key] = collect();
            }
        }
        foreach ($numericKeys as $key) {
            if (!isset($viewData[$key])) {
                $viewData[$key] = 0;
            }
        }

        if (!isset($viewData['machineAttendance'])) {
            $viewData['machineAttendance'] = null;
        }
        if (!isset($viewData['machineAttendanceStatus'])) {
            $viewData['machineAttendanceStatus'] = null;
        }

        return view('dashboard', $viewData);
    }
    /**
     * Get a fresh API token for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getToken(Request $request)
    {
        $user = Auth::user();

        // Optional: delete old tokens to keep it clean, or just add a new one.
        // For a single-page-app experience, we often just want *a* valid token.
        // Let's sweep old tokens to avoid clutter.
        $user->tokens()->delete();

        $token = $user->createToken('spa_token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ]);
    }
}
