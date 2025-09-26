<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        if (auth()->user()->role === 'co_owner' && $serviceOrder->address->area_id !== auth()->user()->area_id) {
            abort(403);
        }

        return view('pages.invoices.create', compact('serviceOrder'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $request->validate([
            'service_order_id' => 'required|exists:service_orders,id',
            'invoice_number' => 'required|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|string|in:fixed,percentage',
            'transport_fee' => 'required|numeric',
        ]);

        $serviceOrder = ServiceOrder::findOrFail($request->service_order_id);

        if (auth()->user()->role === 'co_owner' && $serviceOrder->address->area_id !== auth()->user()->area_id) {
            abort(403);
        }

        $subtotal = $request->subtotal;
        $discount = $request->discount ?? 0;
        $discountType = $request->discount_type;
        $transportFee = $request->transport_fee;

        $discountAmount = 0;
        if ($discountType === 'percentage') {
            $discountAmount = ($subtotal * $discount) / 100;
        } else {
            $discountAmount = $discount;
        }

        $grandTotal = ($subtotal - $discountAmount) + $transportFee;

        $invoice = Invoice::create([
            'service_order_id' => $request->service_order_id,
            'invoice_number' => $request->invoice_number,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_type' => $discountType,
            'transport_fee' => $transportFee,
            'grand_total' => $grandTotal,
            'status' => 'new',
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
    public function destroy(string $id)
    {
        //
    }
}
