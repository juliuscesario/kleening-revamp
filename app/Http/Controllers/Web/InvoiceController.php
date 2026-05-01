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
        // Fetch the most recent non-cancelled invoice to check if an active one exists
        $activeInvoice = Invoice::where('service_order_id', $request->service_order_id)
            ->where('status', '!=', Invoice::STATUS_CANCELLED)
            ->latest()
            ->first();
        // Also fetch any cancelled invoice for the create form context (to reset values)
        $anyInvoice = Invoice::where('service_order_id', $request->service_order_id)
            ->latest()
            ->first();
        $invoice = $anyInvoice;

        if (auth()->user()->role === 'co_owner' && $serviceOrder->address->area_id !== auth()->user()->area_id) {
            abort(403);
        }

        // If an active invoice exists, redirect to it
        if ($activeInvoice) {
            return redirect()->route('web.invoices.show', $activeInvoice);
        }

        // Reset DP and transport for cancelled invoice context
        if ($invoice) {
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
            'notes' => 'nullable|string',
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
        $notes = $request->notes;

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
        $invoice = Invoice::where('service_order_id', $request->service_order_id)
            ->where('status', '!=', Invoice::STATUS_CANCELLED)
            ->latest()
            ->first();

        // If an active invoice exists, prevent creating a new one
        if ($invoice) {
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
                'status' => Invoice::STATUS_SENT,
                'notes' => $notes,
            ]
        );

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
        $invoice = Invoice::with(['serviceOrder.customer', 'serviceOrder.address.area', 'serviceOrder.staff', 'serviceOrder.items.service', 'serviceOrder.workPhotos', 'payments', 'reissueOrigin', 'reissuedInvoice'])->findOrFail($id);
        $this->authorize('view', $invoice);
        return view('pages.invoices.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['serviceOrder.workPhotos']);

        $amountDue = max($invoice->grand_total - $invoice->paid_amount, 0);

        $workPhotos = $invoice->serviceOrder->workPhotos ?? collect();
        $photoArrival = $workPhotos->where('type', 'arrival')->sortByDesc('created_at')->first();
        $photoBefore = $workPhotos->where('type', 'before')->sortByDesc('created_at')->first();
        $photoAfter = $workPhotos->where('type', 'after')->sortByDesc('created_at')->first();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.invoice', compact('invoice', 'amountDue', 'photoArrival', 'photoBefore', 'photoAfter'));

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function viewPdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['serviceOrder.workPhotos']);

        $amountDue = max($invoice->grand_total - $invoice->paid_amount, 0);

        $workPhotos = $invoice->serviceOrder->workPhotos ?? collect();
        $photoArrival = $workPhotos->where('type', 'arrival')->sortByDesc('created_at')->first();
        $photoBefore = $workPhotos->where('type', 'before')->sortByDesc('created_at')->first();
        $photoAfter = $workPhotos->where('type', 'after')->sortByDesc('created_at')->first();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.invoice', compact('invoice', 'amountDue', 'photoArrival', 'photoBefore', 'photoAfter'));

        return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
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

    /**
     * Reissue an invoice: cancel the old one and create a new one with adjusted values.
     */
    public function reissue(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'transport_fee' => 'required|numeric|min:0',
            'discount_type' => 'required|in:none,fixed,percentage',
            'discount' => 'required_unless:discount_type,none|numeric|min:0',
        ]);

        // Guard: cannot reissue a paid or already cancelled invoice
        if ($invoice->status === Invoice::STATUS_PAID) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice yang sudah dibayar tidak bisa di-reissue.',
            ], 422);
        }

        if ($invoice->status === Invoice::STATUS_CANCELLED) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice yang sudah dibatalkan tidak bisa di-reissue.',
            ], 422);
        }

        if ($invoice->payments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice dengan riwayat pembayaran tidak bisa di-reissue.',
            ], 422);
        }

        $newTransportFee = $request->transport_fee;
        $newDiscountType = $request->discount_type;
        $newDiscount = $newDiscountType === 'none' ? 0 : $request->discount;

        // Calculate discount amount
        $discountAmount = 0;
        if ($newDiscountType === 'percentage') {
            if ($newDiscount > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount percentage cannot exceed 100.',
                ], 422);
            }
            $discountAmount = ($invoice->subtotal * $newDiscount) / 100;
        } else {
            $discountAmount = $newDiscount;
        }

        $newGrandTotal = ($invoice->subtotal - $discountAmount) + $newTransportFee;

        if ($newGrandTotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Grand total must be greater than 0.',
            ], 422);
        }

        // Generate new invoice number
        // Use date-based format similar to existing: INV/YYYY/MM/DD
        $newInvoiceNumber = 'INV/' . now()->format('Y/m/d') . '/' . $invoice->service_order_id . '-' . now()->format('His');

        // Ensure uniqueness
        $baseNumber = $newInvoiceNumber;
        $suffix = 1;
        while (Invoice::where('invoice_number', $newInvoiceNumber)->exists()) {
            $newInvoiceNumber = $baseNumber . '-' . $suffix;
            $suffix++;
        }

        DB::beginTransaction();
        try {
            // 1. Cancel the original invoice
            $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

            // Revert service_order status back to done (so we can create new invoice)
            if ($invoice->serviceOrder) {
                $invoice->serviceOrder->update(['status' => ServiceOrder::STATUS_DONE]);
            }

            // 2. Create new invoice
            $newInvoice = Invoice::create([
                'service_order_id' => $invoice->service_order_id,
                'invoice_number' => $newInvoiceNumber,
                'issue_date' => now()->format('Y-m-d'),
                'due_date' => $invoice->due_date, // keep same due date
                'subtotal' => $invoice->subtotal,
                'discount' => $newDiscount,
                'discount_type' => $newDiscountType,
                'transport_fee' => $newTransportFee,
                'grand_total' => $newGrandTotal,
                'dp_type' => $invoice->dp_type,
                'dp_value' => $invoice->dp_value,
                'total_after_dp' => $newGrandTotal - ($invoice->dp_type === 'percentage'
                    ? ($newGrandTotal * $invoice->dp_value) / 100
                    : $invoice->dp_value),
                'paid_amount' => 0, // reset paid amount for new invoice
                'status' => Invoice::STATUS_SENT,
                'notes' => $invoice->notes,
                'reissued_from' => $invoice->id,
            ]);

            // 3. Point service_order to the new invoice
            if ($invoice->serviceOrder) {
                $invoice->serviceOrder->update(['status' => ServiceOrder::STATUS_INVOICED]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil di-reissue.',
                'new_invoice_id' => $newInvoice->id,
                'new_invoice_number' => $newInvoice->invoice_number,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reissue invoice: ' . $e->getMessage(),
            ], 500);
        }
    }
}
