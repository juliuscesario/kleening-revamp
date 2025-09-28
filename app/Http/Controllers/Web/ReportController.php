<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Area;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use AuthorizesRequests;
    public function revenue()
    {
        $this->authorize('viewAny', \App\Models\Invoice::class); // Reuse Invoice policy for report access
        $areas = Area::all();
        return view('pages.reports.revenue', compact('areas'));
    }

    public function staffPerformance()
    {
        $this->authorize('viewAny', \App\Models\Staff::class);
        $areas = Area::all();
        $staff = \App\Models\Staff::with('user')->get();
        return view('pages.reports.staff-performance', compact('areas', 'staff'));
    }

    public function customerGrowth()
    {
        $this->authorize('viewAny', \App\Models\Customer::class);
        $areas = Area::all();
        return view('pages.reports.customer-growth', compact('areas'));
    }
}