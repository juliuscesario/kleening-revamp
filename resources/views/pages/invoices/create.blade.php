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
<div class="page-body" id="create-invoice-page">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Service Order Details</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Customer:</strong> {{ $serviceOrder->customer->name }}</p>
                        <p><strong>Address:</strong> {{ $serviceOrder->address->full_address }}</p>
                        <p><strong>Area:</strong> {{ $serviceOrder->address->area->name }}</p>
                        <p><strong>Staff:</strong> {{ $serviceOrder->staff->pluck('name')->join(', ') }}</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Service Items</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceOrder->items as $item)
                                <tr>
                                    <td>{{ $item->service->name }}</td>
                                    <td>Rp {{ number_format($item->price, 2, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="item-total" data-total="{{ $item->price * $item->quantity }}">Rp {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                 <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Work Photos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($serviceOrder->workPhotos as $photo)
                                <div class="col-md-4">
                                    <img src="{{ $photo->photo_url }}" class="img-fluid mb-2">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
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
                                <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                                <div class="btn-group mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary due-date-btn" data-days="0">Immediately</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary due-date-btn active" data-days="7">7 Days</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary due-date-btn" data-days="14">14 Days</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary due-date-btn" data-days="30">30 Days</button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subtotal</label>
                                <input type="text" class="form-control" id="subtotal_display" value="Rp 0,00" readonly>
                                <input type="hidden" id="subtotal" name="subtotal" value="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Discount</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="discount" name="discount" value="0">
                                    <select class="form-select" id="discount_type" name="discount_type">
                                        <option value="fixed">Fixed</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Transport Fee</label>
                                <input type="number" class="form-control" id="transport_fee" name="transport_fee" value="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Grand Total</label>
                                <input type="text" class="form-control" id="grand_total_display" value="Rp 0,00" readonly>
                                <input type="hidden" id="grand_total" name="grand_total" value="0">
                            </div>
                            <button type="submit" class="btn btn-primary">Create Invoice</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection