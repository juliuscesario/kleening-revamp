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
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'staff') {
            $staffId = $user->staff->id;

            $today = Carbon::today();
            $tomorrow = Carbon::tomorrow();

            // Service Orders for today
            $todayServiceOrders = ServiceOrder::whereHas('staff', function ($query) use ($staffId) {
                $query->where('staff.id', $staffId);
            })
            ->whereDate('work_date', $today)
            ->whereIn('status', ['booked', 'proses'])
            ->orderBy('work_date', 'asc')
            ->get();

            // Service Orders for tomorrow
            $tomorrowServiceOrders = ServiceOrder::whereHas('staff', function ($query) use ($staffId) {
                $query->where('staff.id', $staffId);
            })
            ->whereDate('work_date', $tomorrow)
            ->where('status', 'booked')
            ->orderBy('work_date', 'asc')
            ->get();

            // Past Service Orders that are still booked or in process
            $pastServiceOrders = ServiceOrder::whereHas('staff', function ($query) use ($staffId) {
                $query->where('staff.id', $staffId);
            })
            ->whereDate('work_date', '<', $today)
            ->whereIn('status', ['booked', 'proses'])
            ->orderBy('work_date', 'desc')
            ->get();

            // Cancelled Service Orders
            $cancelledServiceOrders = ServiceOrder::whereHas('staff', function ($query) use ($staffId) {
                $query->where('staff.id', $staffId);
            })
            ->where('status', 'cancelled')
            ->orderBy('work_date', 'desc')
            ->get();

            // Done Service Orders
            $doneServiceOrders = ServiceOrder::whereHas('staff', function ($query) use ($staffId) {
                $query->where('staff.id', $staffId);
            })
            ->where('status', 'done')
            ->orderBy('work_date', 'desc')
            ->get();

            // Statistics
            $totalDoneCount = ServiceOrder::whereHas('staff', function ($query) use ($staffId) {
                $query->where('staff.id', $staffId);
            })
            ->where('status', 'done')
            ->count();

            $todayDoneCount = ServiceOrder::whereHas('staff', function ($query) use ($staffId) {
                $query->where('staff.id', $staffId);
            })
            ->where('status', 'done')
            ->whereDate('updated_at', $today) // Assuming 'done' status updates 'updated_at'
            ->count();

            $bookedCount = ServiceOrder::whereHas('staff', function ($query) use ($staffId) {
                $query->where('staff.id', $staffId);
            })
            ->where('status', 'booked')
            ->count();

            return view('dashboard', compact(
                'todayServiceOrders',
                'tomorrowServiceOrders',
                'pastServiceOrders',
                'cancelledServiceOrders',
                'doneServiceOrders',
                'totalDoneCount',
                'todayDoneCount',
                'bookedCount'
            ));
        }

        // For non-staff users, just return the view with empty collections
        return view('dashboard', [
            'todayServiceOrders' => collect(),
            'tomorrowServiceOrders' => collect(),
            'pastServiceOrders' => collect(),
            'cancelledServiceOrders' => collect(),
            'doneServiceOrders' => collect(),
            'totalDoneCount' => 0,
            'todayDoneCount' => 0,
            'bookedCount' => 0,
        ]);
    }
}