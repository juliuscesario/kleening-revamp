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
use Illuminate\Support\Facades\DB;

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

            // Monthly Activity Counts
            $viewData['soStats'] = [
                'created' => (clone $serviceOrderQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'done'    => (clone $serviceOrderQuery)->where('status', ServiceOrder::STATUS_DONE)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'cancel'  => (clone $serviceOrderQuery)->where('status', ServiceOrder::STATUS_CANCELLED)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            ];

            // Last month SO stats
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $serviceOrderQueryLastMonth = ServiceOrder::query();
            if ($role === 'co-owner') {
                $serviceOrderQueryLastMonth->whereHas('address', fn($q) => $q->where('area_id', $user->area_id));
            }
            $viewData['soStatsLastMonth'] = [
                'created' => (clone $serviceOrderQueryLastMonth)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
                'done'    => (clone $serviceOrderQueryLastMonth)->where('status', ServiceOrder::STATUS_DONE)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
                'cancel'  => (clone $serviceOrderQueryLastMonth)->where('status', ServiceOrder::STATUS_CANCELLED)->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
            ];

            $invoiceQueryOwner = Invoice::withoutGlobalScopes()->whereBetween('invoices.created_at', [$startOfMonth, $endOfMonth]);
            if ($role === 'owner') {
                // owner sees all areas
            } else {
                $invoiceQueryOwner->whereHas('serviceOrder.address', fn($q) => $q->where('area_id', $user->area_id));
            }
            $viewData['invoiceStats'] = [
                'created' => (clone $invoiceQueryOwner)->count(),
                'sent'    => (clone $invoiceQueryOwner)->where('status', Invoice::STATUS_SENT)->count(),
                'paid'    => (clone $invoiceQueryOwner)->where('status', Invoice::STATUS_PAID)->count(),
                'cancel'  => (clone $invoiceQueryOwner)->where('status', Invoice::STATUS_CANCELLED)->count(),
                'overdue' => (clone $invoiceQueryOwner)->where('status', Invoice::STATUS_OVERDUE)->count(),
            ];

            // Last month invoice stats
            $invoiceQueryLastMonth = Invoice::withoutGlobalScopes()->whereBetween('invoices.created_at', [$lastMonthStart, $lastMonthEnd]);
            if ($role === 'co-owner') {
                $invoiceQueryLastMonth->whereHas('serviceOrder.address', fn($q) => $q->where('area_id', $user->area_id));
            }
            $viewData['invoiceStatsLastMonth'] = [
                'created' => (clone $invoiceQueryLastMonth)->count(),
                'sent'    => (clone $invoiceQueryLastMonth)->where('status', Invoice::STATUS_SENT)->count(),
                'paid'    => (clone $invoiceQueryLastMonth)->where('status', Invoice::STATUS_PAID)->count(),
                'cancel'  => (clone $invoiceQueryLastMonth)->where('status', Invoice::STATUS_CANCELLED)->count(),
                'overdue' => (clone $invoiceQueryLastMonth)->where('status', Invoice::STATUS_OVERDUE)->count(),
            ];

            // Card 5 — SO Tanpa Invoice
            $viewData['soWithoutInvoice'] = (clone $serviceOrderQuery)
                ->where('status', ServiceOrder::STATUS_DONE)
                ->doesntHave('invoice')
                ->count();

            // Card 6 — Rata-rata Umur Invoice (PostgreSQL)
            $avgInvoiceAge = Invoice::withoutGlobalScopes()
                ->where('status', Invoice::STATUS_PAID)
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->selectRaw("AVG(DATE_PART('day', updated_at - created_at)) as avg_days")
                ->value('avg_days');
            $viewData['avgInvoiceAge'] = round($avgInvoiceAge ?? 0);

            // Revenue Trend (6 months)
            $rawTrend = Invoice::withoutGlobalScopes()
                ->where('status', Invoice::STATUS_PAID)
                ->where('updated_at', '>=', now()->subMonths(5)->startOfMonth())
                ->selectRaw("TO_CHAR(updated_at, 'YYYY-MM') as month, SUM(grand_total) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month')
                ->toArray();

            // Fill missing months with 0
            $revenueTrend = collect();
            for ($i = 5; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $revenueTrend->put($key, $rawTrend[$key] ?? 0);
            }
            $viewData['revenueTrend'] = $revenueTrend;

            // Revenue by Category (this month)
            $viewData['revenueByCategory'] = Invoice::withoutGlobalScopes()
                ->where('invoices.status', Invoice::STATUS_PAID)
                ->whereMonth('invoices.updated_at', now()->month)
                ->whereYear('invoices.updated_at', now()->year)
                ->join('service_orders', 'service_orders.id', '=', 'invoices.service_order_id')
                ->join('service_order_items', 'service_order_items.service_order_id', '=', 'service_orders.id')
                ->join('services', 'services.id', '=', 'service_order_items.service_id')
                ->join('service_categories', 'service_categories.id', '=', 'services.category_id')
                ->selectRaw('service_categories.name as category, SUM(invoices.grand_total) as total')
                ->groupBy('service_categories.name')
                ->pluck('total', 'category');

            // Invoice Aging
            $now = now();
            $unpaidStatuses = [Invoice::STATUS_NEW, Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE];
            $viewData['invoiceAging'] = [
                '0-7 hari' => Invoice::withoutGlobalScopes()
                    ->whereIn('status', $unpaidStatuses)
                    ->where('created_at', '>=', $now->copy()->subDays(7))
                    ->sum('grand_total'),
                '8-14 hari' => Invoice::withoutGlobalScopes()
                    ->whereIn('status', $unpaidStatuses)
                    ->whereBetween('created_at', [$now->copy()->subDays(14), $now->copy()->subDays(7)])
                    ->sum('grand_total'),
                '15-30 hari' => Invoice::withoutGlobalScopes()
                    ->whereIn('status', $unpaidStatuses)
                    ->whereBetween('created_at', [$now->copy()->subDays(30), $now->copy()->subDays(14)])
                    ->sum('grand_total'),
                '30+ hari' => Invoice::withoutGlobalScopes()
                    ->whereIn('status', $unpaidStatuses)
                    ->where('created_at', '<', $now->copy()->subDays(30))
                    ->sum('grand_total'),
            ];

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

            // Admin Scorecard (for owner/coowner — admin performance)
            $admins = \App\Models\User::where('role', 'admin')->get();
            $viewData['adminScorecard'] = $admins->map(function ($admin) {
                $invoicesCount = \App\Models\Invoice::whereMonth('invoices.created_at', now()->month)
                    ->whereYear('invoices.created_at', now()->year)
                    ->join('service_orders', 'service_orders.id', '=', 'invoices.service_order_id')
                    ->where('service_orders.created_by', $admin->id)
                    ->count('invoices.id');

                $avgTurnaround = \App\Models\Invoice::whereMonth('invoices.created_at', now()->month)
                    ->whereYear('invoices.created_at', now()->year)
                    ->join('service_orders', 'service_orders.id', '=', 'invoices.service_order_id')
                    ->where('service_orders.created_by', $admin->id)
                    ->where('service_orders.status', ServiceOrder::STATUS_DONE)
                    ->selectRaw("AVG(DATE_PART('day', invoices.created_at - service_orders.updated_at)) as avg_days")
                    ->value('avg_days');

                return [
                    'name' => $admin->name,
                    'invoices_count' => $invoicesCount,
                    'avg_turnaround' => round($avgTurnaround ?? 0, 1),
                ];
            });

            // Bottleneck Alerts
            $bottlenecks = collect();

            // SOs stuck in proses > 2 days
            $stuckProses = ServiceOrder::where('status', ServiceOrder::STATUS_PROSES)
                ->where('updated_at', '<', now()->subDays(2))
                ->with('customer:id,name')
                ->orderBy('updated_at', 'asc')
                ->limit(3)
                ->get()
                ->map(fn($so) => [
                    'type' => 'so_stuck',
                    'label' => "SO #{$so->so_number} — {$so->customer->name}",
                    'detail' => 'Proses > ' . round(now()->diffInDays($so->updated_at)) . ' hari',
                    'url' => route('web.service-orders.show', $so->id),
                    'severity' => 'warning',
                ]);

            // SOs done without invoice > 1 day
            $doneNoInvoice = ServiceOrder::where('status', ServiceOrder::STATUS_DONE)
                ->doesntHave('invoice')
                ->where('updated_at', '<', now()->subDay())
                ->with('customer:id,name')
                ->orderBy('updated_at', 'asc')
                ->limit(3)
                ->get()
                ->map(fn($so) => [
                    'type' => 'no_invoice',
                    'label' => "SO #{$so->so_number} — {$so->customer->name}",
                    'detail' => 'Done ' . round(now()->diffInDays($so->updated_at)) . ' hari, belum ada invoice',
                    'url' => route('web.service-orders.show', $so->id),
                    'severity' => 'danger',
                ]);

            // Invoices unpaid > 14 days
            $overdueInvoices = Invoice::where('status', '!=', Invoice::STATUS_PAID)
                ->where('created_at', '<', now()->subDays(14))
                ->with('serviceOrder.customer:id,name')
                ->orderBy('created_at', 'asc')
                ->limit(3)
                ->get()
                ->map(fn($inv) => [
                    'type' => 'overdue_invoice',
                    'label' => "Invoice #{$inv->invoice_number} — Rp " . number_format($inv->grand_total, 0, ',', '.'),
                    'detail' => 'Unpaid ' . round(now()->diffInDays($inv->created_at)) . ' hari',
                    'url' => route('web.invoices.show', $inv->id),
                    'severity' => 'danger',
                ]);

            $viewData['bottlenecks'] = $stuckProses->concat($doneNoInvoice)->concat($overdueInvoices)
                ->sortBy(function ($item) {
                    return $item['severity'] === 'danger' ? 0 : 1;
                })
                ->take(5)
                ->values();

            // Staff Leaderboard — only users with role=staff, grouped by area
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $activeStaff = \App\Models\Staff::where('is_active', true)
                ->whereHas('user', function ($q) {
                    $q->where('role', 'staff');
                })
                ->with('area')
                ->get();

            // Precompute sessions per staff via raw join for speed
            $sessionCounts = DB::table('order_session_staff')
                ->join('order_sessions', 'order_sessions.id', '=', 'order_session_staff.order_session_id')
                ->where('order_sessions.type', 'kerja')
                ->whereBetween('order_sessions.tanggal', [$monthStart, $monthEnd])
                ->selectRaw('staff_id, count(*) as sessions')
                ->groupBy('staff_id')
                ->pluck('sessions', 'staff_id');

            // Precompute photo compliance: count SOs with both before+after photos per staff this month
            $staffSOs = DB::table('order_session_staff')
                ->join('order_sessions', 'order_sessions.id', '=', 'order_session_staff.order_session_id')
                ->where('order_sessions.type', 'kerja')
                ->whereBetween('order_sessions.tanggal', [$monthStart, $monthEnd])
                ->select('staff_id', 'order_sessions.service_order_id')
                ->get();

            // Group SOs by staff_id
            $staffSOGrouped = $staffSOs->groupBy('staff_id')->map(function ($group) {
                return $group->pluck('service_order_id')->unique();
            });

            // Bulk check work_photos: get all photos for relevant SOs
            $allSOIds = $staffSOGrouped->flatten()->unique();
            $photoTypes = DB::table('work_photos')
                ->whereIn('service_order_id', $allSOIds)
                ->whereIn('type', ['before', 'after'])
                ->get()
                ->groupBy('service_order_id');

            $photoComplianceMap = [];
            foreach ($staffSOGrouped as $staffId => $soIds) {
                $total = $soIds->count();
                if ($total === 0) {
                    $photoComplianceMap[$staffId] = 100;
                    continue;
                }
                $withBoth = 0;
                foreach ($soIds as $soId) {
                    $photos = $photoTypes[$soId] ?? collect();
                    if ($photos->contains('type', 'before') && $photos->contains('type', 'after')) {
                        $withBoth++;
                    }
                }
                $photoComplianceMap[$staffId] = round(($withBoth / $total) * 100);
            }

            $viewData['staffLeaderboard'] = $activeStaff->map(function ($staff) use ($sessionCounts, $photoComplianceMap) {
                return [
                    'name' => $staff->name,
                    'area' => optional($staff->area)->name ?? '-',
                    'sessions' => $sessionCounts[$staff->id] ?? 0,
                    'photo_compliance' => $photoComplianceMap[$staff->id] ?? 100,
                ];
            })->sortByDesc('sessions')->take(10)->values();

            // ── Customer Insights (Owner/Co-owner only) ──
            $now = now();
            $monthStart = $now->copy()->startOfMonth();

            // Total unique customers with SOs this month
            $totalCustomersThisMonth = ServiceOrder::withoutGlobalScopes()
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->distinct('customer_id')
                ->count('customer_id');

            // New customers created this month
            $newCustomers = Customer::withoutGlobalScopes()
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->count();

            // Repeat customers: had SOs before this month AND have SOs this month
            $repeatCustomers = DB::table('service_orders as so1')
                ->whereMonth('so1.created_at', $now->month)
                ->whereYear('so1.created_at', $now->year)
                ->whereExists(function ($q) use ($monthStart) {
                    $q->select(DB::raw(1))
                        ->from('service_orders as so2')
                        ->whereColumn('so2.customer_id', 'so1.customer_id')
                        ->where('so2.created_at', '<', $monthStart);
                })
                ->distinct('so1.customer_id')
                ->count('so1.customer_id');

            $repeatRate = $totalCustomersThisMonth > 0
                ? round(($repeatCustomers / $totalCustomersThisMonth) * 100)
                : 0;

            $viewData['customerOverview'] = [
                'total' => $totalCustomersThisMonth,
                'new' => $newCustomers,
                'repeat' => $repeatCustomers,
                'repeat_rate' => $repeatRate,
            ];

            // Top 5 customers by revenue
            $topCustomerData = DB::table('customers')
                ->select(
                    'customers.name',
                    DB::raw('COUNT(DISTINCT service_orders.id) as total_orders'),
                    DB::raw("COALESCE(SUM(CASE WHEN invoices.id IS NOT NULL THEN invoices.grand_total ELSE 0 END), 0) as total_revenue"),
                    DB::raw('MAX(service_orders.created_at) as last_order_at')
                )
                ->join('service_orders', 'service_orders.customer_id', '=', 'customers.id')
                ->leftJoin('invoices', 'invoices.service_order_id', '=', 'service_orders.id')
                ->groupBy('customers.id', 'customers.name')
                ->orderByDesc('total_revenue')
                ->limit(5)
                ->get()
                ->map(function ($c) {
                    $c->days_since_last = $c->last_order_at ? round(now()->diffInDays($c->last_order_at)) : null;
                    return $c;
                });

            $viewData['topCustomers'] = $topCustomerData;

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
            'areaPerformance',
            'revenueTrend',
            'revenueByCategory',
            'todaySchedule',
            'unassignedJobs',
            'tomorrowSchedule',
            'todaySessions',
            'tomorrowSessions',
            'pastSessions',
            'cancelledSessions',
            'doneSessions',
            'adminScorecard',
            'bottlenecks',
            'staffLeaderboard',
            'customerOverview',
            'topCustomers',
        ];
        $arrayKeys = [
            'invoiceAging',
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
            'soWithoutInvoice',
            'avgInvoiceAge',
            'totalDoneCount',
            'todayDoneCount',
            'bookedCount',
            'soStats',
            'invoiceStats',
            'soStatsLastMonth',
            'invoiceStatsLastMonth',
        ];

        foreach ($collectionKeys as $key) {
            if (!isset($viewData[$key])) {
                $viewData[$key] = collect();
            }
        }
        foreach ($arrayKeys as $key) {
            if (!isset($viewData[$key])) {
                $viewData[$key] = [];
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
