<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Area;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
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

    public function revenueDrilldown(\App\Models\ServiceCategory $serviceCategory)
    {
        $this->authorize('viewAny', \App\Models\Invoice::class);
        return view('pages.reports.revenue-drilldown', compact('serviceCategory'));
    }

    public function staffDrilldown(\App\Models\Staff $staff)
    {
        $this->authorize('view', $staff);
        return view('pages.reports.staff-drilldown', compact('staff'));
    }

    public function customerDrilldown($customerId)
    {
        $customer = \App\Models\Customer::withTrashed()->findOrFail($customerId);
        $this->authorize('view', $customer);
        return view('pages.reports.customer-drilldown', compact('customer'));
    }

    public function profitability(Request $request)
    {
        $this->authorize('viewAny', Invoice::class); // Reuse policy
        $areas = Area::all();
        return view('pages.reports.profitability', compact('areas'));
    }

    public function staffUtilization(Request $request)
    {
        $this->authorize('viewAny', Staff::class); // Reuse policy
        $areas = Area::all();
        return view('pages.reports.staff_utilization', compact('areas'));
    }
}