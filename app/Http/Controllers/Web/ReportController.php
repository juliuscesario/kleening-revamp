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

    public function __construct()
    {
        $this->authorize('view-reports');
    }

    public function revenue()
    {
        $areas = Area::all();
        return view('pages.reports.revenue', compact('areas'));
    }

    public function staffPerformance()
    {
        $areas = Area::all();
        $staff = \App\Models\Staff::with('user')->get();
        return view('pages.reports.staff-performance', compact('areas', 'staff'));
    }

    public function customerGrowth()
    {
        $areas = Area::all();
        return view('pages.reports.customer-growth', compact('areas'));
    }

    public function revenueDrilldown(\App\Models\ServiceCategory $serviceCategory)
    {
        return view('pages.reports.revenue-drilldown', compact('serviceCategory'));
    }

    public function staffDrilldown(\App\Models\Staff $staff)
    {
        return view('pages.reports.staff-drilldown', compact('staff'));
    }

    public function customerDrilldown($customerId)
    {
        $customer = \App\Models\Customer::withTrashed()->findOrFail($customerId);
        return view('pages.reports.customer-drilldown', compact('customer'));
    }

    public function profitability(Request $request)
    {
        $areas = Area::all();
        return view('pages.reports.profitability', compact('areas'));
    }

    public function staffUtilization(Request $request)
    {
        $areas = Area::all();
        return view('pages.reports.staff_utilization', compact('areas'));
    }

    public function invoiceAging(Request $request)
    {
        $invoices = Invoice::with('serviceOrder.customer')
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])
            ->get()
            ->map(function ($invoice) {
                $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                $now = \Carbon\Carbon::now();
                $daysOverdue = $now->diffInDays($dueDate, false); // false to get negative for overdue

                $agingBucket = 'Current';
                if ($daysOverdue < 0) { // If due date is in the past
                    $absDaysOverdue = abs($daysOverdue);
                    if ($absDaysOverdue <= 30) {
                        $agingBucket = '1-30 Days Overdue';
                    } elseif ($absDaysOverdue <= 60) {
                        $agingBucket = '31-60 Days Overdue';
                    } elseif ($absDaysOverdue <= 90) {
                        $agingBucket = '61-90 Days Overdue';
                    } else {
                        $agingBucket = '90+ Days Overdue';
                    }
                }

                $invoice->days_overdue = $daysOverdue;
                $invoice->aging_bucket = $agingBucket;
                return $invoice;
            });

        return view('pages.reports.invoice_aging', compact('invoices'));
    }
}