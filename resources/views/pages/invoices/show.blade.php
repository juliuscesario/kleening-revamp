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
                <div class="col-auto d-print-none">
                    <div class="btn-list d-flex flex-wrap justify-content-end">
                        @if($invoice->status === 'new')
                            <button class="btn btn-info change-status-btn" data-id="{{ $invoice->id }}"
                                data-new-status="sent">Mark as Sent</button>
                        @endif
                        @if($invoice->status === 'sent' || $invoice->status === 'overdue')
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markAsPaidModal">Make
                                Payment</button>
                        @endif
                        @if(
                                in_array(strtolower(auth()->user()->role), ['owner', 'admin'])
                                && $invoice->status !== \App\Models\Invoice::STATUS_PAID
                                && $invoice->status !== \App\Models\Invoice::STATUS_CANCELLED
                            )
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reissueModal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.933 13.041a8 8 0 1 1 -9.925 -8.788c3.899 -1 7.935 1.007 9.425 4.747" /><path d="M20 4v5h-5" /></svg>
                                Reissue Invoice
                            </button>
                            <form method="POST" action="{{ route('web.invoices.destroy', $invoice) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"
                                    onclick="return confirm('Yakin ingin membatalkan invoice ini?');">
                                    Cancel Invoice
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('web.invoices.view-pdf', $invoice) }}" class="btn btn-outline-primary" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" fill="none">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                            </svg>
                            View Invoice
                        </a>
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

            {{-- Cross-link notice: this invoice was reissued from a cancelled one --}}
            @if($invoice->reissueOrigin)
            <div class="alert alert-info mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2.417m-.992 4.585c-.192 .314 -.425 .607 -.694 .867c-1.215 1.174 -3.039 1.427 -4.544 .626l-.12 -.066c-1.464 -.818 -2.227 -2.406 -1.892 -3.982l.033 -.15c.352 -1.653 1.834 -2.857 3.596 -2.91l1.097 -.033c1.19 -.036 2.356 -.452 3.307 -1.174" /><path d="M12 3v6" /></svg>
                <strong>Reissue dari</strong>
                <a href="{{ route('web.invoices.show', $invoice->reissueOrigin) }}">{{ $invoice->reissueOrigin->invoice_number }}</a>
            </div>
            @endif

            {{-- Cross-link notice: this cancelled invoice was replaced by a new one --}}
            @if($invoice->status === \App\Models\Invoice::STATUS_CANCELLED && $invoice->reissuedInvoice)
            <div class="alert alert-warning mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 .001z" /><path d="M12 16h.01" /></svg>
                <strong>Invoice ini telah dibatalkan dan diganti dengan</strong>
                <a href="{{ route('web.invoices.show', $invoice->reissuedInvoice) }}">{{ $invoice->reissuedInvoice->invoice_number }}</a>
            </div>
            @endif
            <div class="card card-lg">
                <div class="card-body table-responsive">
                    <div class="row">
                        <div class="col-6">
                            <p class="h3">{{ $invoice->serviceOrder->customer->name }}</p>
                            <address>
                                {{ $invoice->serviceOrder->address->full_address }}<br>
                                {{ $invoice->serviceOrder->address->area->name }}
                            </address>
                            <div class="mt-3">
                                <p class="mb-1"><strong>Catatan Invoice:</strong></p>
                                <p class="text-muted mb-2">{{ $invoice->notes ?? 'Tidak ada catatan.' }}
                                </p>
                                <p class="mb-1"><strong>Catatan Internal (Staff):</strong></p>
                                <p class="text-muted">{{ $invoice->serviceOrder->staff_notes ?? 'Tidak ada catatan.' }}</p>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            @php
                                $statusBadgeClass = match ($invoice->status) {
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
                            <p><strong>Status:</strong> <span
                                    class="badge {{ $statusBadgeClass }} text-bg-secondary">{{ ucfirst($invoice->status) }}</span>
                            </p>
                            @php
                                $staffNames = $invoice->serviceOrder->staff->pluck('name');
                            @endphp
                            @if($staffNames->isNotEmpty())
                                <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                    <strong>Staff:</strong> {{ implode(', ', $staffNames->toArray()) }}
                                </p>
                            @endif

                        </div>
                    </div>
                    <table class="table table-transparent">
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
                            <td class="font-weight-bold text-end">Rp {{ number_format($invoice->grand_total, 2, ',', '.') }}
                            </td>
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
                    </table>

                    {{-- Payment History Section --}}
                    <div class="row mt-4">
                        <div class="col-12">
                            <h3 class="card-title mb-3">Payment History</h3>
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Method</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoice->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->payment_date }}</td>
                                                <td>{{ $payment->reference_number ?? '-' }}</td>
                                                <td>{{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}</td>
                                                <td class="text-end">Rp {{ number_format($payment->amount, 2, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No payments recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="strong text-end">Total Final Paid</td>
                                            <td class="text-end">Rp
                                                {{ number_format($invoice->payments->sum('amount') + ($invoice->dp_type === 'percentage' ? ($invoice->grand_total * $invoice->dp_value) / 100 : $invoice->dp_value), 2, ',', '.') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="strong text-uppercase text-end">Remaining Balance</td>
                                            <td class="font-weight-bold text-end">Rp
                                                {{ number_format($invoice->balance, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @php
                        $wp = $invoice->serviceOrder->workPhotos;
                        $wpArrival = $wp->where('type', 'arrival')->sortByDesc('created_at')->first();
                        $wpBefore = $wp->where('type', 'before')->sortByDesc('created_at')->first();
                        $wpAfter = $wp->where('type', 'after')->sortByDesc('created_at')->first();
                    @endphp
                    @if($wpArrival && $wpBefore && $wpAfter)
                    <div class="card mt-3" style="{{ $invoice->serviceOrder->items->count() > 5 ? 'page-break-before:always;' : '' }}">
                        <div class="card-header">
                            <h3 class="card-title">Work Photos</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <img src="{{ $wpArrival->photo_url }}" class="img-fluid mb-2" alt="Arrival">
                                    <div class="text-center text-muted small">Arrival</div>
                                </div>
                                <div class="col-md-4">
                                    <img src="{{ $wpBefore->photo_url }}" class="img-fluid mb-2" alt="Before">
                                    <div class="text-center text-muted small">Before</div>
                                </div>
                                <div class="col-md-4">
                                    <img src="{{ $wpAfter->photo_url }}" class="img-fluid mb-2" alt="After">
                                    <div class="text-center text-muted small">After</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
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
                            <input type="number" class="form-control" name="amount" value="{{ $invoice->balance }}">
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
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="savePaymentBtn">Save Payment</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Reissue Invoice Modal --}}
    <div class="modal modal-blur fade" id="reissueModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reissue Invoice {{ $invoice->invoice_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">This will cancel the current invoice and create a new one with updated values.</p>

                    {{-- Current values (read-only) --}}
                    <div class="mb-3">
                        <h6 class="text-muted">Current Values</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Subtotal</td>
                                <td class="text-end" id="currentSubtotal">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Transport</td>
                                <td class="text-end" id="currentTransport">Rp {{ number_format($invoice->transport_fee, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Discount</td>
                                <td class="text-end" id="currentDiscount">
                                    @if($invoice->discount_type === 'percentage')
                                        {{ $invoice->discount }}%
                                    @else
                                        Rp {{ number_format($invoice->discount ?? 0, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted"><strong>Grand Total</strong></td>
                                <td class="text-end"><strong id="currentGrandTotal">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</strong></td>
                            </tr>
                        </table>
                    </div>

                    <hr>

                    {{-- New values (form) --}}
                    <form id="reissueForm">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <h6 class="mb-3">New Values</h6>
                        <div class="mb-3">
                            <label class="form-label">Transport Fee</label>
                            <input type="number" class="form-control" name="transport_fee" id="newTransportFee" value="{{ $invoice->transport_fee }}" min="0" step="1000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Discount Type</label>
                            <select class="form-select" name="discount_type" id="newDiscountType">
                                <option value="none" {{ !$invoice->discount ? 'selected' : '' }}>Tanpa Diskon</option>
                                <option value="fixed" {{ $invoice->discount_type === 'fixed' ? 'selected' : '' }}>Nominal (Rp)</option>
                                <option value="percentage" {{ $invoice->discount_type === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="discountValueWrapper" style="{{ $invoice->discount ? '' : 'display:none;' }}">
                            <label class="form-label">Discount Value</label>
                            <input type="number" class="form-control" name="discount" id="newDiscountValue" value="{{ $invoice->discount ?? 0 }}" min="0" step="1000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Grand Total Preview</label>
                            <div class="p-3 bg-light rounded text-end h4 mb-0" id="newGrandTotalPreview">
                                Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="reissueBtn">
                        <span id="reissueBtnSpinner" class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
                        Reissue Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
(function() {
    const subtotal = {{ $invoice->subtotal }};
    const transportField = document.getElementById('newTransportFee');
    const discountTypeField = document.getElementById('newDiscountType');
    const discountValueField = document.getElementById('newDiscountValue');
    const discountWrapper = document.getElementById('discountValueWrapper');
    const grandTotalPreview = document.getElementById('newGrandTotalPreview');
    const reissueBtn = document.getElementById('reissueBtn');
    const reissueBtnSpinner = document.getElementById('reissueBtnSpinner');

    function formatCurrency(val) {
        return 'Rp ' + Number(val).toLocaleString('id-ID');
    }

    function recalcGrandTotal() {
        const transport = parseFloat(transportField.value) || 0;
        const discountType = discountTypeField.value;
        const discountValue = parseFloat(discountValueField.value) || 0;

        let discountAmount = 0;
        if (discountType === 'percentage') {
            discountAmount = (subtotal * discountValue) / 100;
        } else if (discountType === 'fixed') {
            discountAmount = discountValue;
        }

        const grandTotal = (subtotal - discountAmount) + transport;
        grandTotalPreview.textContent = formatCurrency(Math.max(grandTotal, 0));

        // Color the preview red if grand total is 0 or negative
        if (grandTotal <= 0) {
            grandTotalPreview.classList.add('text-danger');
        } else {
            grandTotalPreview.classList.remove('text-danger');
        }
    }

    // Toggle discount value visibility
    discountTypeField.addEventListener('change', function() {
        if (this.value === 'none') {
            discountWrapper.style.display = 'none';
            discountValueField.value = 0;
        } else {
            discountWrapper.style.display = '';
        }
        recalcGrandTotal();
    });

    transportField.addEventListener('input', recalcGrandTotal);
    discountValueField.addEventListener('input', recalcGrandTotal);

    // Submit reissue
    reissueBtn.addEventListener('click', function() {
        const oldNumber = '{{ $invoice->invoice_number }}';
        if (!confirm('Invoice ' + oldNumber + ' akan dibatalkan dan invoice baru akan dibuat. Lanjutkan?')) {
            return;
        }

        reissueBtn.disabled = true;
        reissueBtnSpinner.classList.remove('d-none');

        const formData = new FormData();
        formData.append('transport_fee', transportField.value);
        formData.append('discount_type', discountTypeField.value);
        formData.append('discount', discountTypeField.value === 'none' ? 0 : discountValueField.value);

        fetch('{{ route('web.invoices.reissue', $invoice->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                }).then(() => {
                    window.location.href = '{{ url('invoices') }}/' + data.new_invoice_id;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                });
                reissueBtn.disabled = false;
                reissueBtnSpinner.classList.add('d-none');
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat memproses reissue.',
            });
            reissueBtn.disabled = false;
            reissueBtnSpinner.classList.add('d-none');
        });
    });
})();
</script>
@endpush
@endsection