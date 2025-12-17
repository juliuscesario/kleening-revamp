<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ServiceOrder;
use App\Models\Invoice;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InvoiceController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.invoices.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Invoice::class);
        $serviceOrder = ServiceOrder::with(['items.service', 'customer', 'address.area', 'staff', 'workPhotos'])->findOrFail($request->service_order_id);
        $invoice = Invoice::where('service_order_id', $request->service_order_id)->first();

        if (auth()->user()->role === 'co_owner' && $serviceOrder->address->area_id !== auth()->user()->area_id) {
            abort(403);
        }

        if ($invoice && $invoice->status === Invoice::STATUS_CANCELLED) {
            // If the invoice was cancelled, we allow creating a new one.
            // We can reset any values if needed, e.g., DP or transport fee.
            $invoice->dp_value = 0;
            $invoice->transport_fee = 0;
        }


        return view('pages.invoices.create', compact('serviceOrder', 'invoice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'service_order_id' => 'required|exists:service_orders,id',
            'invoice_number' => 'required',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|string|in:fixed,percentage',
            'transport_fee' => 'required|numeric',
            'dp_value' => 'nullable|numeric',
            'dp_type' => 'nullable|string|in:fixed,percentage',
        ]);

        $serviceOrder = ServiceOrder::findOrFail($request->service_order_id);

        if (auth()->user()->role === 'co_owner' && $serviceOrder->address->area_id !== auth()->user()->area_id) {
            abort(403);
        }

        $subtotal = $request->subtotal;
        $discount = $request->discount ?? 0;
        $discountType = $request->discount_type;
        $transportFee = $request->transport_fee;
        $dpValue = $request->dp_value ?? 0;
        $dpType = $request->dp_type;

        $discountAmount = 0;
        if ($discountType === 'percentage') {
            $discountAmount = ($subtotal * $discount) / 100;
        } else {
            $discountAmount = $discount;
        }

        $grandTotal = ($subtotal - $discountAmount) + $transportFee;

        $dpAmount = 0;
        if ($dpType === 'percentage') {
            $dpAmount = ($grandTotal * $dpValue) / 100;
        } else {
            $dpAmount = $dpValue;
        }

        $totalAfterDp = $grandTotal - $dpAmount;

        // Check for an existing invoice for this service order
        $invoice = Invoice::where('service_order_id', $request->service_order_id)->first();

        // If an invoice exists and it's not cancelled, prevent creating a new one
        if ($invoice && $invoice->status !== Invoice::STATUS_CANCELLED) {
            return redirect()->back()->with('error', 'An active invoice for this service order already exists.');
        }

        // Check if the new invoice number is unique, ignoring the current invoice if it exists
        $uniqueInvoice = Invoice::where('invoice_number', $request->invoice_number)
                                ->when($invoice, function ($query) use ($invoice) {
                                    return $query->where('id', '!=', $invoice->id);
                                })
                                ->exists();

        if ($uniqueInvoice) {
            return redirect()->back()->withErrors(['invoice_number' => 'The invoice number has already been taken.'])->withInput();
        }

        // If no invoice exists, or if it was cancelled, create or update it
        $invoice = Invoice::updateOrCreate(
            ['service_order_id' => $request->service_order_id],
            [
            'invoice_number' => $request->invoice_number,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_type' => $discountType,
            'transport_fee' => $transportFee,
            'grand_total' => $grandTotal,
            'dp_type' => $dpType,
            'dp_value' => $dpValue,
            'total_after_dp' => $totalAfterDp,
            'paid_amount' => $dpAmount, // Assuming DP is paid upon invoice creation
            'status' => Invoice::STATUS_NEW,
        ]);

        $serviceOrder->update(['status' => ServiceOrder::STATUS_INVOICED]);

        return redirect()->route('web.invoices.show', $invoice);
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'status' => 'required|string|in:new,sent,overdue,paid',
        ]);

        $invoice->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Invoice status updated successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with(['serviceOrder.customer', 'serviceOrder.address.area', 'serviceOrder.staff', 'serviceOrder.items.service', 'serviceOrder.workPhotos'])->findOrFail($id);
        $this->authorize('view', $invoice);
        return view('pages.invoices.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.invoice', compact('invoice'));

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        if ($invoice->status === Invoice::STATUS_PAID) {
            return redirect()
                ->route('web.invoices.show', $invoice->id)
                ->with('error', 'Invoice yang sudah dibayar tidak bisa dibatalkan.');
        }

        if ($invoice->status === Invoice::STATUS_CANCELLED) {
            return redirect()
                ->route('web.invoices.show', $invoice->id)
                ->with('error', 'Invoice ini sudah berstatus dibatalkan.');
        }

        if ($invoice->payments()->exists()) {
            return redirect()
                ->route('web.invoices.show', $invoice->id)
                ->with('error', 'Invoice dengan riwayat pembayaran tidak bisa dibatalkan.');
        }

        DB::transaction(function () use ($invoice) {
            if ($invoice->serviceOrder) {
                $invoice->serviceOrder->update(['status' => ServiceOrder::STATUS_DONE]);
            }
            $invoice->update(['status' => Invoice::STATUS_CANCELLED]);
        });

        return redirect()
            ->route('web.invoices.show', $invoice->id)
            ->with('success', 'Invoice berhasil dibatalkan dan akan tetap muncul sebagai arsip.');
    }
}
