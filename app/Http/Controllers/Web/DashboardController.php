<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with filtered service orders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $todayServiceOrders = collect();
        $tomorrowServiceOrders = collect(); // Renamed from tomorrow for clarity
        $pastServiceOrders = collect();
        $cancelledServiceOrders = collect();
        $allBookedServiceOrders = collect();
        $allProsesServiceOrders = collect();

        if ($user->role === 'staff' && $user->staff) {
            $staffId = $user->staff->id;

            // Base query for the authenticated staff member
            $baseQuery = ServiceOrder::whereHas('staff', function ($q) use ($staffId) {
                $q->where('staff.id', $staffId);
            })->with(['customer', 'address', 'items.service', 'staff']);

            // Efficiently fetch data with separate queries
            $today = Carbon::today();

            $todayServiceOrders = $baseQuery->clone()
                ->whereDate('work_date', $today)
                ->where('status', '!=', 'cancelled')
                ->orderBy('work_date', 'asc')
                ->get();

            $tomorrowServiceOrders = $baseQuery->clone()
                ->whereDate('work_date', '>', $today)
                ->where('status', '!=', 'cancelled')
                ->orderBy('work_date', 'asc')
                ->get();

            $pastServiceOrders = $baseQuery->clone()
                ->whereDate('work_date', '<', $today)
                ->where('status', '!=', 'cancelled')
                ->orderBy('work_date', 'desc') // Often better to show recent past first
                ->get();

            $cancelledServiceOrders = $baseQuery->clone()
                ->where('status', 'cancelled')
                ->orderBy('work_date', 'desc')
                ->get();

            $allBookedServiceOrders = $baseQuery->clone()
                ->where('status', 'booked')
                ->get();

            $allProsesServiceOrders = $baseQuery->clone()
                ->where('status', 'proses')
                ->get();
        }

        return view('dashboard', compact(
            'todayServiceOrders',
            'tomorrowServiceOrders', // Updated variable name
            'pastServiceOrders',
            'cancelledServiceOrders',
            'allBookedServiceOrders',
            'allProsesServiceOrders'
        ));
    }
}