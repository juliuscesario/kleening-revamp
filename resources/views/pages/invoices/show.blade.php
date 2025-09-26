@extends('layouts.admin')

@section('title', 'Invoice #' . $invoice->invoice_number)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Invoice #{{ $invoice->invoice_number }}
                </h2>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card card-lg">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="h3">{{ $invoice->serviceOrder->customer->name }}</p>
                        <address>
                            {{ $invoice->serviceOrder->address->full_address }}<br>
                            {{ $invoice->serviceOrder->address->area->name }}
                        </address>
                    </div>
                    <div class="col-6 text-end">
                        <p class="h3">Invoice</p>
                        <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                        <p><strong>Issue Date:</strong> {{ $invoice->issue_date }}</p>
                        <p><strong>Due Date:</strong> {{ $invoice->due_date }}</p>
                        <p><strong>Status:</strong> <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 'danger' }} text-bg-secondary">{{ ucfirst($invoice->status) }}</span></p>
                    </div>
                </div>
                <table class="table table-transparent table-responsive">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Service</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    @foreach($invoice->serviceOrder->items as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <p class="strong mb-1">{{ $item->service->name }}</p>
                        </td>
                        <td class="text-center">
                            {{ $item->quantity }}
                        </td>
                        <td class="text-end">Rp {{ number_format($item->price, 2, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="strong text-end">Subtotal</td>
                        <td class="text-end">Rp {{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="strong text-end">
                            Discount
                            @if($invoice->discount_type === 'percentage')
                                <span class="badge bg-primary text-bg-primary ms-2">{{ $invoice->discount }}%</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($invoice->discount_type === 'percentage')
                                - Rp {{ number_format(($invoice->subtotal * $invoice->discount) / 100, 2, ',', '.') }}
                            @else
                                - Rp {{ number_format($invoice->discount, 2, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="strong text-end">Transport Fee</td>
                        <td class="text-end">Rp {{ number_format($invoice->transport_fee, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="font-weight-bold text-uppercase text-end">Grand Total</td>
                        <td class="font-weight-bold text-end">Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}</td>
                    </tr>
                </table>
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Work Photos</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($invoice->serviceOrder->workPhotos as $photo)
                                <div class="col-md-4">
                                    <img src="{{ $photo->photo_url }}" class="img-fluid mb-2">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
