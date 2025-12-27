<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Area;

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

        if ($user->role === 'owner' || $user->role === 'co_owner') {
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

        } elseif ($user->role === 'admin') {
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

            $viewData['recentActivity'] = ServiceOrder::where('created_by', $user->id)
                ->latest()
                ->take(10)
                ->get();

        } elseif ($user->role === 'staff') {
            // Existing staff logic...
            $staffId = $user->staff->id;
            $tomorrow = Carbon::tomorrow();
            $startOfMonth = $today->copy()->subMonthNoOverflow()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();

            $viewData['todayServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))
                ->whereDate('work_date', $today)
                ->whereIn('status', ['booked', 'proses'])
                ->orderBy('work_date', 'desc')
                ->orderByRaw("COALESCE(work_time, '23:59:59') asc")
                ->get();

            $viewData['tomorrowServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))
                ->whereDate('work_date', $tomorrow)
                ->where('status', 'booked')
                ->orderBy('work_date', 'desc')
                ->orderByRaw("COALESCE(work_time, '23:59:59') asc")
                ->get();

            $viewData['pastServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))
                ->whereBetween('work_date', [$today->copy()->subDays(6), $today->copy()->subDay()])
                ->whereIn('status', ['booked', 'proses'])
                ->orderBy('work_date', 'desc')
                ->orderByRaw("COALESCE(work_time, '23:59:59') asc")
                ->get();

            $viewData['cancelledServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))
                ->where('status', 'cancelled')
                ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
                ->orderBy('work_date', 'desc')
                ->orderByRaw("COALESCE(work_time, '23:59:59') asc")
                ->get();

            $viewData['doneServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))
                ->where('status', 'done')
                // ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
                ->where('work_date', '>=', $today->copy()->subDays(10)) // Last 10 days
                ->with(['customer', 'address']) // Eager load relations
                ->orderBy('work_date', 'desc')
                ->orderByRaw("COALESCE(work_time, '23:59:59') asc")
                ->get();
            $viewData['totalDoneCount'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->where('status', 'done')->count();
            $viewData['todayDoneCount'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->where('status', 'done')->whereDate('work_date', $today)->count();
            $viewData['bookedCount'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->where('status', 'booked')->whereDate('work_date', '>=', $today)->count();
        }

        // Default empty values for keys not set in every role
        $collectionKeys = [
            'dailyRevenue',
            'areaPerformance',
            'todaySchedule',
            'unassignedJobs',
            'recentActivity',
            'todayServiceOrders',
            'tomorrowServiceOrders',
            'pastServiceOrders',
            'cancelledServiceOrders',
            'doneServiceOrders'
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
