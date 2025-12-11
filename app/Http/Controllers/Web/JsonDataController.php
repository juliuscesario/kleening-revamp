<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use App\Models\ServiceOrder;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables; // Add this import
use Carbon\Carbon;

class JsonDataController extends Controller
{


    public function customerAddresses(Customer $customer)
    {
        return $customer->addresses()->with('area')->get();
    }

    public function staffByArea(Area $area)
    {
        $user = Auth::user();
        $query = Staff::query()->where('area_id', $area->id);

        if ($user->role == 'co_owner' && $user->area_id != $area->id) {
            return response()->json([], 403);
        }

        return $query->get();
    }

    public function services(Request $request)
    {
        $query = Service::query();

        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        return $query->get();
    }

    public function customerPendingServiceOrders(Customer $customer)
    {
        $activeStatuses = [
            ServiceOrder::STATUS_BOOKED,
            ServiceOrder::STATUS_PROSES,
        ];

        $pendingOrders = $customer->serviceOrders()
            ->whereIn('status', $activeStatuses)
            ->select(['id', 'so_number', 'status', 'work_date'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'so_number' => $order->so_number,
                    'status' => $order->status,
                    'work_date' => optional($order->work_date)->toDateString(),
                ];
            });

        $now = Carbon::now();

        $overdueInvoices = $customer->invoices()
            ->whereNotIn('invoices.status', [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])
            ->whereDate('invoices.due_date', '<', $now)
            ->select([
                'invoices.id',
                'invoices.invoice_number',
                'invoices.status',
                'invoices.due_date',
            ])
            ->orderBy('invoices.due_date', 'asc')
            ->get()
            ->map(function ($invoice) use ($now) {
                $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date) : null;

                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'due_date' => optional($dueDate)->toDateString(),
                    'overdue_days' => $dueDate ? $dueDate->diffInDays($now) : null,
                ];
            });

        return response()->json([
            'has_pending' => $pendingOrders->isNotEmpty(),
            'pending_count' => $pendingOrders->count(),
            'orders' => $pendingOrders,
            'has_overdue_invoices' => $overdueInvoices->isNotEmpty(),
            'overdue_invoice_count' => $overdueInvoices->count(),
            'overdue_invoices' => $overdueInvoices,
        ]);
    }
}
