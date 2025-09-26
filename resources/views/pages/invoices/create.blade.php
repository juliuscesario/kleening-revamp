@extends('layouts.admin')

@section('title', 'Create Invoice')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Create Invoice for Service Order #{{ $serviceOrder->id }}
                </h2>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('web.invoices.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="service_order_id" value="{{ $serviceOrder->id }}">
                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control" name="invoice_number" value="{{ 'INV-' . date('Ymd') . '-' . $serviceOrder->id }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issue Date</label>
                        <input type="date" class="form-control" name="issue_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subtotal</label>
                        <input type="number" class="form-control" name="subtotal" value="{{ $serviceOrder->total_price }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transport Fee</label>
                        <input type="number" class="form-control" name="transport_fee" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grand Total</label>
                        <input type="number" class="form-control" name="grand_total" value="{{ $serviceOrder->total_price }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
