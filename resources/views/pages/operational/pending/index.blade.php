@extends('layouts.admin')
@section('title', 'Pending Items')

@php
function daysBadgeClass($days) {
    if ($days > 7) return 'bg-danger';
    if ($days >= 3) return 'bg-warning text-dark';
    return '';
}
@endphp

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Pending</h2>
                <div class="text-secondary mt-1">Track forgotten Service Orders & Invoices</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body p-0">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-alt" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-so" type="button" role="tab" aria-selected="true">
                            <i class="ti ti-clipboard-text me-1"></i>
                            Service Orders
                            @if(count($pendingSOs) > 0)
                                <span class="badge bg-red ms-2">{{ count($pendingSOs) }}</span>
                            @else
                                <span class="badge bg-muted ms-2">0</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-invoice" type="button" role="tab" aria-selected="false">
                            <i class="ti ti-receipt me-1"></i>
                            Invoices
                            @if(count($unpaidInvoices) > 0)
                                <span class="badge bg-red ms-2">{{ count($unpaidInvoices) }}</span>
                            @else
                                <span class="badge bg-muted ms-2">0</span>
                            @endif
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    {{-- Tab 1: Service Orders --}}
                    <div class="tab-pane fade show active" id="tab-so" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>SO Number</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Days Pending</th>
                                        <th class="w-1">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingSOs as $so)
                                    <tr>
                                        <td>
                                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="text-primary fw-semibold">
                                                {{ $so->so_number }}
                                            </a>
                                        </td>
                                        <td>{{ $so->customer->name ?? '—' }}</td>
                                        <td>{{ $so->service_category_name ?? '—' }}</td>
                                        <td>
                                            @if($so->status === 'proses')
                                                <span class="badge bg-warning text-dark">Proses</span>
                                            @elseif($so->status === 'done')
                                                <span class="badge bg-success">Done</span>
                                            @else
                                                <span class="badge bg-muted">{{ ucfirst($so->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $so->created_at->format('d M Y') }}</td>
                                        <td>
                                            @if($so->days_pending > 7)
                                                <span class="badge bg-danger">⚠ {{ $so->days_pending }} days</span>
                                            @elseif($so->days_pending >= 3)
                                                <span class="badge bg-warning text-dark">{{ $so->days_pending }} days</span>
                                            @else
                                                <span>{{ $so->days_pending }} days</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            ✅ All clear — no pending Service Orders
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tab 2: Invoices --}}
                    <div class="tab-pane fade" id="tab-invoice" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Invoice Number</th>
                                        <th>SO Number</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Sent Date</th>
                                        <th>Days Unpaid</th>
                                        <th class="w-1">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unpaidInvoices as $inv)
                                    <tr>
                                        <td>
                                            <a href="{{ route('web.invoices.show', $inv->id) }}" class="text-primary fw-semibold">
                                                {{ $inv->invoice_number }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($inv->serviceOrder)
                                                <a href="{{ route('web.service-orders.show', $inv->serviceOrder->id) }}" class="text-primary">
                                                    {{ $inv->serviceOrder->so_number }}
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $inv->serviceOrder->customer->name ?? '—' }}</td>
                                        <td>Rp {{ number_format($inv->grand_total, 0, '.', '.') }}</td>
                                        <td>{{ $inv->created_at->format('d M Y') }}</td>
                                        <td>
                                            @if($inv->days_unpaid > 7)
                                                <span class="badge bg-danger">⚠ {{ $inv->days_unpaid }} days</span>
                                            @elseif($inv->days_unpaid >= 3)
                                                <span class="badge bg-warning text-dark">{{ $inv->days_unpaid }} days</span>
                                            @else
                                                <span>{{ $inv->days_unpaid }} days</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('web.invoices.show', $inv->id) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            ✅ All clear — no unpaid Invoices
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
