@extends('layouts.admin')

@section('title', 'Payment Details')

@section('content')
@php
    $invoice = $payment->invoice;
    $serviceOrder = optional($invoice)->serviceOrder;
    $formattedPaymentDate = $payment->payment_date
        ? \Illuminate\Support\Carbon::parse($payment->payment_date)->format('d M Y')
        : null;
@endphp

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Payment Details
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @if($invoice)
                        <a href="{{ route('web.invoices.show', $invoice) }}" class="btn btn-primary" target="_blank">
                            View Invoice
                        </a>
                        <a href="{{ route('web.invoices.download', $invoice) }}" class="btn btn-outline-primary" target="_blank">
                            Download Invoice PDF
                        </a>
                    @endif
                    @if($serviceOrder)
                        <a href="{{ route('web.service-orders.show', $serviceOrder) }}" class="btn btn-warning text-dark">
                            View Service Order
                        </a>
                    @endif
                    <a href="{{ route('web.payments.index') }}" class="btn btn-secondary">
                        Back to Payments
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards gy-3">
            <div class="col-12 col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">Payment Summary</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-5 col-sm-4">Invoice</dt>
                            <dd class="col-7 col-sm-8">{{ $invoice?->invoice_number ?? '-' }}</dd>
                            <dt class="col-5 col-sm-4">Reference</dt>
                            <dd class="col-7 col-sm-8">{{ $payment->reference_number ?? '-' }}</dd>
                            <dt class="col-5 col-sm-4">Payment Date</dt>
                            <dd class="col-7 col-sm-8">{{ $formattedPaymentDate ?? $payment->payment_date ?? '-' }}</dd>
                            <dt class="col-5 col-sm-4">Amount</dt>
                            <dd class="col-7 col-sm-8">Rp {{ number_format($payment->amount, 2, ',', '.') }}</dd>
                            <dt class="col-5 col-sm-4">Method</dt>
                            <dd class="col-7 col-sm-8 text-capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            @if($invoice)
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Invoice Overview</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-5 col-sm-4">Status</dt>
                                <dd class="col-7 col-sm-8">
                                    <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 'warning' }} text-bg-secondary">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </dd>
                                <dt class="col-5 col-sm-4">Issue Date</dt>
                                <dd class="col-7 col-sm-8">{{ $invoice->issue_date ?? '-' }}</dd>
                                <dt class="col-5 col-sm-4">Due Date</dt>
                                <dd class="col-7 col-sm-8">{{ $invoice->due_date ?? '-' }}</dd>
                                <dt class="col-5 col-sm-4">Total</dt>
                                <dd class="col-7 col-sm-8">Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}</dd>
                                <dt class="col-5 col-sm-4">Balance</dt>
                                <dd class="col-7 col-sm-8">Rp {{ number_format($invoice->balance, 2, ',', '.') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endif
            @if($serviceOrder)
                <div class="col-12 col-xl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Service Order</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-5 col-sm-4">SO Number</dt>
                                <dd class="col-7 col-sm-8">{{ $serviceOrder->so_number }}</dd>
                                <dt class="col-5 col-sm-4">Status</dt>
                                <dd class="col-7 col-sm-8">
                                    <span class="badge bg-primary text-bg-primary">{{ ucfirst($serviceOrder->status) }}</span>
                                </dd>
                                <dt class="col-5 col-sm-4">Work Date</dt>
                                <dd class="col-7 col-sm-8">{{ $serviceOrder->work_date ? \Carbon\Carbon::parse($serviceOrder->work_date)->format('d M Y') : '-' }}</dd>
                                <dt class="col-5 col-sm-4">Work Time (WIB)</dt>
                                <dd class="col-7 col-sm-8">{{ $serviceOrder->work_time_formatted ? $serviceOrder->work_time_formatted . ' WIB' : '-' }}</dd>
                                <dt class="col-5 col-sm-4">Customer</dt>
                                <dd class="col-7 col-sm-8">{{ $serviceOrder->customer?->name ?? '-' }}</dd>
                                <dt class="col-5 col-sm-4">Address</dt>
                                <dd class="col-7 col-sm-8">{{ $serviceOrder->address?->full_address ?? '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Assigned Staff</h3>
                        </div>
                        <div class="card-body">
                            @if($serviceOrder->staff->isNotEmpty())
                                <div class="row gy-2">
                                    @foreach($serviceOrder->staff as $staff)
                                        <div class="col-12 col-md-6">
                                            <div class="card card-sm">
                                                <div class="card-body">
                                                    <p class="mb-1 fw-bold">{{ $staff->name }}</p>
                                                    <p class="mb-0 text-muted">{{ $staff->phone_number ?? 'No phone number' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No staff assigned to this service order.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            @if($payment->notes)
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Notes</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $payment->notes }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
