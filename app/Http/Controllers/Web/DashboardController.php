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

            // KPIs
            $viewData['monthlyRevenue'] = Invoice::where('status', Invoice::STATUS_PAID)
                ->whereBetween('updated_at', [$startOfMonth, $today])
                ->sum('grand_total');

            $viewData['jobsCompletedThisMonth'] = ServiceOrder::whereIn('status', [ServiceOrder::STATUS_DONE, ServiceOrder::STATUS_INVOICED])
                ->whereBetween('work_date', [$startOfMonth, $today])
                ->count();

            $viewData['outstandingInvoices'] = Invoice::whereIn('status', [Invoice::STATUS_NEW, Invoice::STATUS_SENT])->sum('grand_total');
            $viewData['overdueInvoices'] = Invoice::where('status', Invoice::STATUS_OVERDUE)->sum('grand_total');
            $viewData['newCustomersThisMonth'] = Customer::whereBetween('created_at', [$startOfMonth, $today])->count();

            // Service Order Funnel
            $funnelDate = $today->copy()->subDays(30);
            $viewData['funnelBooked'] = ServiceOrder::where('status', ServiceOrder::STATUS_BOOKED)->where('created_at', '>=', $funnelDate)->count();
            $viewData['funnelProses'] = ServiceOrder::where('status', ServiceOrder::STATUS_PROSES)->where('created_at', '>=', $funnelDate)->count();
            $viewData['funnelInvoiced'] = ServiceOrder::where('status', ServiceOrder::STATUS_INVOICED)->where('created_at', '>=', $funnelDate)->count();

            // Monthly Revenue Chart (Daily)
            $viewData['dailyRevenue'] = Payment::selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                ->whereBetween('payment_date', [$startOfMonth, $today])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            // Area Performance (Owner only)
            if ($user->role === 'owner') {
                $areas = Area::all();

                $jobsCount = ServiceOrder::join('addresses', 'service_orders.address_id', '=', 'addresses.id')
                    ->whereIn('service_orders.status', [ServiceOrder::STATUS_DONE, ServiceOrder::STATUS_INVOICED])
                    ->whereBetween('work_date', [$startOfMonth, $today])
                    ->selectRaw('addresses.area_id, count(service_orders.id) as count')
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
                ->orderBy('created_at', 'asc')
                ->get();

            $viewData['unassignedJobs'] = ServiceOrder::whereDoesntHave('staff')
                ->where('status', ServiceOrder::STATUS_BOOKED)
                ->get();

            $viewData['recentActivity'] = ServiceOrder::where('created_by', $user->id)
                ->latest()
                ->take(10)
                ->get();

        } elseif ($user->role === 'staff') {
            // Existing staff logic...
            $staffId = $user->staff->id;
            $tomorrow = Carbon::tomorrow();

            $viewData['todayServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->whereDate('work_date', $today)->whereIn('status', ['booked', 'proses'])->orderBy('work_date', 'asc')->get();
            $viewData['tomorrowServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->whereDate('work_date', $tomorrow)->where('status', 'booked')->orderBy('work_date', 'asc')->get();
            $viewData['pastServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->whereDate('work_date', '<', $today)->whereIn('status', ['booked', 'proses'])->orderBy('work_date', 'desc')->get();
            $viewData['cancelledServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->where('status', 'cancelled')->orderBy('work_date', 'desc')->get();
            $viewData['doneServiceOrders'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->where('status', 'done')->orderBy('work_date', 'desc')->get();
            $viewData['totalDoneCount'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->where('status', 'done')->count();
            $viewData['todayDoneCount'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->where('status', 'done')->whereDate('updated_at', $today)->count();
            $viewData['bookedCount'] = ServiceOrder::whereHas('staff', fn($q) => $q->where('staff.id', $staffId))->where('status', 'booked')->count();
        }

        // Default empty values for keys not set in every role
        $collectionKeys = [
            'dailyRevenue', 'areaPerformance', 'todaySchedule', 'unassignedJobs', 'recentActivity',
            'todayServiceOrders', 'tomorrowServiceOrders', 'pastServiceOrders', 'cancelledServiceOrders', 'doneServiceOrders'
        ];
        $numericKeys = [
            'monthlyRevenue', 'jobsCompletedThisMonth', 'outstandingInvoices', 'overdueInvoices', 'newCustomersThisMonth',
            'funnelBooked', 'funnelProses', 'funnelInvoiced',
            'totalDoneCount', 'todayDoneCount', 'bookedCount'
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
}