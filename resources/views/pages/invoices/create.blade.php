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
        <form action="{{ route('web.invoices.store') }}" method="POST">
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
                        <div class="mt-3">
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Catatan Invoice (Akan muncul di PDF)</label>
                                <textarea class="form-control" name="notes" rows="3" placeholder="Masukkan catatan untuk invoice...">{{ $invoice->notes ?? $serviceOrder->work_notes }}</textarea>
                            </div>
                            <p><strong>Catatan Internal:</strong> {{ $serviceOrder->staff_notes ?? 'Tidak ada catatan.' }}</p>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Service Items</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="items-table">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceOrder->items as $index => $item)
                                    <tr>
                                        <td>
                                            <select name="items[{{ $index }}][service_id]" class="form-select service-select">
                                                @foreach($services as $service)
                                                    <option value="{{ $service->id }}" data-price="{{ $service->price }}" {{ $service->id == $item->service_id ? 'selected' : '' }}>{{ $service->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][price]" class="form-control item-price" value="{{ $item->price }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" value="{{ $item->quantity }}">
                                        </td>
                                        <td class="item-total-display">Rp {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-success btn-sm mt-2" id="add-item">Add New Item</button>
                        </div>
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
                            @csrf
                            <input type="hidden" name="service_order_id" value="{{ $serviceOrder->id }}">
                            <div class="mb-3">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" class="form-control" name="invoice_number" value="{{ $invoice->invoice_number ?? 'INV-' . date('Ymd') . '-' . $serviceOrder->id }}" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Issue Date</label>
                                <input type="date" class="form-control" id="issue_date" name="issue_date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                <div class="btn-group mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary due-date-btn" data-days="0">Immediately</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary due-date-btn active" data-days="1">1 Day</button>
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
                                <input type="number" class="form-control" id="transport_fee" name="transport_fee" value="{{ $invoice->transport_fee ?? 0 }}">
                                <div class="btn-group mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary transport-fee-btn active" data-fee="0">No Fee</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary transport-fee-btn" data-fee="25000">25K</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary transport-fee-btn" data-fee="50000">50K</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary transport-fee-btn" data-fee="75000">75K</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary transport-fee-btn" data-fee="100000">100K</button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Down Payment (DP)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="dp_value" name="dp_value" value="{{ $invoice->dp_value ?? 0 }}">
                                    <select class="form-select" id="dp_type" name="dp_type">
                                        <option value="fixed">Fixed</option>
                                        <option value="percentage">Percentage</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Grand Total</label>
                                <input type="text" class="form-control" id="grand_total_display" value="Rp 0,00" readonly>
                                <input type="hidden" id="grand_total" name="grand_total" value="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total After DP</label>
                                <input type="text" class="form-control" id="total_after_dp_display" value="Rp 0,00" readonly>
                                <input type="hidden" id="total_after_dp" name="total_after_dp" value="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Balance</label>
                                <input type="text" class="form-control" id="balance_display" value="Rp 0,00" readonly>
                                <input type="hidden" id="balance" name="balance" value="0">
                            </div>
                            <button type="submit" class="btn btn-primary">Create Invoice</button>

                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const subtotalElement = document.getElementById('subtotal');
    const subtotalDisplayElement = document.getElementById('subtotal_display');
    const discountElement = document.getElementById('discount');
    const discountTypeElement = document.getElementById('discount_type');
    const transportFeeElement = document.getElementById('transport_fee');
    const grandTotalElement = document.getElementById('grand_total');
    const grandTotalDisplayElement = document.getElementById('grand_total_display');
    const dpValueElement = document.getElementById('dp_value');
    const dpTypeElement = document.getElementById('dp_type');
    const totalAfterDpElement = document.getElementById('total_after_dp');
    const totalAfterDpDisplayElement = document.getElementById('total_after_dp_display');
    const balanceElement = document.getElementById('balance');
    const balanceDisplayElement = document.getElementById('balance_display');
    const issueDateElement = document.getElementById('issue_date');
    const dueDateElement = document.getElementById('due_date');
    const dueDateButtons = document.querySelectorAll('.due-date-btn');
    const transportFeeButtons = document.querySelectorAll('.transport-fee-btn');

    function formatCurrency(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('#items-table tbody tr').forEach(function(row) {
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const total = price * quantity;
            row.querySelector('.item-total-display').innerText = formatCurrency(total);
            subtotal += total;
        });

        const discount = parseFloat(discountElement.value) || 0;
        const discountType = discountTypeElement.value;
        const transportFee = parseFloat(transportFeeElement.value) || 0;
        const dpValue = parseFloat(dpValueElement.value) || 0;
        const dpType = dpTypeElement.value;

        let discountAmount = 0;
        if (discountType === 'percentage') {
            discountAmount = (subtotal * discount) / 100;
        } else {
            discountAmount = discount;
        }

        const grandTotal = (subtotal - discountAmount) + transportFee;

        let dpAmount = 0;
        if (dpType === 'percentage') {
            dpAmount = (grandTotal * dpValue) / 100;
        } else {
            dpAmount = dpValue;
        }

        const totalAfterDp = grandTotal - dpAmount;
        const balance = grandTotal - dpAmount;

        subtotalElement.value = subtotal;
        subtotalDisplayElement.value = formatCurrency(subtotal);
        grandTotalElement.value = grandTotal;
        grandTotalDisplayElement.value = formatCurrency(grandTotal);
        totalAfterDpElement.value = totalAfterDp;
        totalAfterDpDisplayElement.value = formatCurrency(totalAfterDp);
        balanceElement.value = balance;
        balanceDisplayElement.value = formatCurrency(balance);
    }

    document.querySelectorAll('#discount, #discount_type, #transport_fee, #dp_value, #dp_type').forEach(function(element) {
        element.addEventListener('input', calculateTotals);
    });

    function calculateDueDate(issueDateValue, daysToAdd) {
        if (!issueDateValue) {
            return '';
        }
        const issueDate = new Date(issueDateValue);
        if (Number.isNaN(issueDate.getTime())) {
            return '';
        }
        issueDate.setDate(issueDate.getDate() + daysToAdd);
        return issueDate.toISOString().slice(0, 10);
    }

    function updateDueDateByDays(days) {
        const nextDate = calculateDueDate(issueDateElement.value, days);
        if (nextDate) {
            dueDateElement.value = nextDate;
        }
    }

    function getActiveDueDateDays() {
        const activeButton = document.querySelector('.due-date-btn.active');
        return activeButton ? parseInt(activeButton.dataset.days, 10) : 0;
    }

    function refreshDueDateBasedOnActiveButton() {
        updateDueDateByDays(getActiveDueDateDays());
    }

    // Due date buttons
    dueDateButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            dueDateButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateDueDateByDays(parseInt(this.dataset.days, 10));
        });
    });

    issueDateElement.addEventListener('change', refreshDueDateBasedOnActiveButton);

    // Transport fee buttons
    function setActiveTransportFeeButton(value) {
        transportFeeButtons.forEach(function(btn) {
            const feeValue = parseInt(btn.dataset.fee, 10);
            btn.classList.toggle('active', feeValue === value);
        });
    }

    transportFeeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const selectedFee = parseInt(this.dataset.fee, 10);
            transportFeeElement.value = selectedFee;
            setActiveTransportFeeButton(selectedFee);
            calculateTotals();
        });
    });

    transportFeeElement.addEventListener('input', function() {
        const manualFee = parseInt(this.value, 10) || 0;
        setActiveTransportFeeButton(manualFee);
        calculateTotals();
    });

    setActiveTransportFeeButton(parseInt(transportFeeElement.value, 10) || 0);

    setActiveTransportFeeButton(parseInt(transportFeeElement.value, 10) || 0);

    let itemIndex = {{ count($serviceOrder->items) }};
    const servicesData = @json($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => $s->price]));

    document.getElementById('add-item').addEventListener('click', function() {
        let optionsHtml = '';
        servicesData.forEach(service => {
            optionsHtml += `<option value="${service.id}" data-price="${service.price}">${service.name}</option>`;
        });

        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="items[${itemIndex}][service_id]" class="form-select service-select">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][price]" class="form-control item-price" value="${servicesData.length > 0 ? servicesData[0].price : 0}">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" value="1">
            </td>
            <td class="item-total-display">Rp 0,00</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
            </td>
        `;
        document.querySelector('#items-table tbody').appendChild(newRow);
        itemIndex++;
        calculateTotals();
    });

    document.querySelector('#items-table').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('tr').remove();
            calculateTotals();
        }
    });

    document.querySelector('#items-table').addEventListener('change', function(e) {
        if (e.target.classList.contains('service-select')) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const price = selectedOption.dataset.price;
            e.target.closest('tr').querySelector('.item-price').value = price;
            calculateTotals();
        }
    });

    document.querySelector('#items-table').addEventListener('input', function(e) {
        if (e.target.classList.contains('item-price') || e.target.classList.contains('item-quantity')) {
            calculateTotals();
        }
    });

    calculateTotals();
    refreshDueDateBasedOnActiveButton();
});
</script>
@endpush
