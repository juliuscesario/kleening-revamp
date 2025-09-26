@extends('layouts.admin')

@section('title', 'Payment Details')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Payment Details
                </h2>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <p><strong>Invoice Number:</strong> {{ $payment->invoice->invoice_number }}</p>
                <p><strong>Reference Number:</strong> {{ $payment->reference_number }}</p>
                <p><strong>Payment Date:</strong> {{ $payment->payment_date }}</p>
                <p><strong>Amount:</strong> Rp {{ number_format($payment->amount, 2, ',', '.') }}</p>
                <p><strong>Payment Method:</strong> {{ $payment->payment_method }}</p>
                <p><strong>Notes:</strong> {{ $payment->notes }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
