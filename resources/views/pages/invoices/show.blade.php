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
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @if($invoice->status === 'new')
                        <button class="btn btn-info change-status-btn" data-id="{{ $invoice->id }}" data-new-status="sent">Mark as Sent</button>
                    @endif
                    @if($invoice->status === 'sent' || $invoice->status === 'overdue')
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markAsPaidModal">Make Payment</button>
                    @endif
                    @if(
                        strtolower(auth()->user()->role) === 'owner'
                        && $invoice->status !== \App\Models\Invoice::STATUS_PAID
                        && $invoice->status !== \App\Models\Invoice::STATUS_CANCELLED
                    )
                        <form method="POST" action="{{ route('web.invoices.destroy', $invoice) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Yakin ingin membatalkan invoice ini?');">
                                Cancel Invoice
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('web.invoices.download', $invoice) }}" class="btn btn-primary">Download PDF</a>
                    <a href="{{ route('web.invoices.index') }}" class="btn">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="page-body" id="invoice-show-page">
    <div class="container-xl">
        @if (session('success'))
            <div class="alert alert-success mb-3" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mb-3" role="alert">
                {{ session('error') }}
            </div>
        @endif
        <div class="card card-lg">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="h3">{{ $invoice->serviceOrder->customer->name }}</p>
                        <address>
                            {{ $invoice->serviceOrder->address->full_address }}<br>
                            {{ $invoice->serviceOrder->address->area->name }}
                        </address>
                        <div class="mt-3">
                            <p class="mb-1"><strong>Catatan Invoice:</strong></p>
                            <p class="text-muted mb-2">{{ $invoice->serviceOrder->work_notes ?? 'Tidak ada catatan.' }}</p>
                            <p class="mb-1"><strong>Catatan Internal (Staff):</strong></p>
                            <p class="text-muted">{{ $invoice->serviceOrder->staff_notes ?? 'Tidak ada catatan.' }}</p>
                        </div>
                    </div>
                    <div class="col-6 text-end">
                        @php
                            $statusBadgeClass = match($invoice->status) {
                                \App\Models\Invoice::STATUS_NEW => 'bg-primary',
                                \App\Models\Invoice::STATUS_SENT => 'bg-info',
                                \App\Models\Invoice::STATUS_OVERDUE => 'bg-warning',
                                \App\Models\Invoice::STATUS_PAID => 'bg-success',
                                \App\Models\Invoice::STATUS_CANCELLED => 'bg-secondary',
                                default => 'bg-dark'
                            };
                        @endphp
                        <p class="h3">Invoice</p>
                        <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                        <p><strong>Issue Date:</strong> {{ $invoice->issue_date }}</p>
                        <p><strong>Due Date:</strong> {{ $invoice->due_date }}</p>
                        <p><strong>Status:</strong> <span class="badge {{ $statusBadgeClass }} text-bg-secondary">{{ ucfirst($invoice->status) }}</span></p>

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
                    <tr>
                        <td colspan="4" class="strong text-end">Down Payment</td>
                        <td class="text-end">
                            @if($invoice->dp_type === 'percentage')
                                - Rp {{ number_format(($invoice->grand_total * $invoice->dp_value) / 100, 2, ',', '.') }}
                            @else
                                - Rp {{ number_format($invoice->dp_value, 2, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="strong text-end">Total After DP</td>
                        <td class="text-end">Rp {{ number_format($invoice->total_after_dp, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="font-weight-bold text-uppercase text-end">Balance</td>
                        <td class="font-weight-bold text-end">Rp {{ number_format($invoice->balance, 2, ',', '.') }}</td>
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

<!-- Mark as Paid Modal -->
<div class="modal modal-blur fade" id="markAsPaidModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Invoice as Paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="markAsPaidForm">
                    <input type="hidden" id="invoice_id" name="invoice_id" value="{{ $invoice->id }}">
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" class="form-control" name="reference_number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" value="{{ $invoice->grand_total }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="qr_payment">QR Payment</option>
                            <option value="virtual_account">Virtual Account</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes"></textarea>
                    </div >
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savePaymentBtn">Save Payment</button>
            </div>
        </div>
    </div>
</div>
@endsection
