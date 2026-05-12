<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!in_array(strtolower(trim($user->role)), ['admin', 'owner'])) {
            return redirect()->route('dashboard');
        }

        // Tab 1: Service Orders with status proses or done that have no invoice
        $pendingSOs = ServiceOrder::whereIn('status', [
                ServiceOrder::STATUS_PROSES,
                ServiceOrder::STATUS_DONE,
            ])
            ->whereDoesntHave('invoice')
            ->with(['customer', 'items.service.category'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($so) {
                $so->days_pending = (int) Carbon::parse($so->created_at)->diffInDays(now());
                $so->service_category_name = $so->items->first()?->service?->category?->name;
                return $so;
            });

        // Tab 2: Invoices that are sent but unpaid
        $unpaidInvoices = Invoice::whereIn('status', [
                Invoice::STATUS_NEW,
                Invoice::STATUS_SENT,
                Invoice::STATUS_OVERDUE,
            ])
            ->where('status', '!=', Invoice::STATUS_PAID)
            ->where('status', '!=', Invoice::STATUS_CANCELLED)
            ->with(['serviceOrder.customer'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($invoice) {
                $invoice->days_unpaid = (int) Carbon::parse($invoice->created_at)->diffInDays(now());
                return $invoice;
            });

        return view('pages.operational.pending.index', [
            'pendingSOs' => $pendingSOs,
            'unpaidInvoices' => $unpaidInvoices,
        ]);
    }
}
