<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Invoice;
use App\Models\Payment;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.payments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Payment::class);
        $invoice = Invoice::findOrFail($request->invoice_id);

        if (auth()->user()->role === 'co_owner' && $invoice->serviceOrder->address->area_id !== auth()->user()->area_id) {
            abort(403);
        }

        return view('pages.payments.create', compact('invoice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Payment::class);

        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'reference_number' => 'nullable|string',
            'amount' => 'required|numeric',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        if (auth()->user()->role === 'co_owner' && $invoice->serviceOrder->address->area_id !== auth()->user()->area_id) {
            abort(403);
        }

        $payment = Payment::create($request->all());

        $invoice->update(['status' => Invoice::STATUS_PAID]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Payment created successfully.']);
        }

        return redirect()->route('web.payments.show', $payment);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = Payment::with([
            'invoice.serviceOrder.customer',
            'invoice.serviceOrder.address.area',
            'invoice.serviceOrder.staff',
        ])->findOrFail($id);
        $this->authorize('view', $payment);
        return view('pages.payments.show', compact('payment'));
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
