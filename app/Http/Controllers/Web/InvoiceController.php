<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ServiceOrder;
use App\Models\Invoice;

class InvoiceController extends Controller
{
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
        $serviceOrder = ServiceOrder::findOrFail($request->service_order_id);

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
            'transport_fee' => 'required|numeric',
            'grand_total' => 'required|numeric',
        ]);

        $serviceOrder = ServiceOrder::findOrFail($request->service_order_id);

        if (auth()->user()->role === 'co_owner' && $serviceOrder->address->area_id !== auth()->user()->area_id) {
            abort(403);
        }

        $invoice = Invoice::create($request->all());

        return redirect()->route('web.invoices.show', $invoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
