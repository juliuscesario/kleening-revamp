@extends('layouts.admin')
@section('title', 'Follow Up Pelanggan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Follow Up Pelanggan</h2>
                <div class="text-muted mt-1">Pelanggan yang sudah lama tidak menggunakan layanan.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        {{-- Summary Stats --}}
        <div class="d-flex gap-3 mb-3 flex-wrap">
            <div class="badge bg-blue-lt fs-5">Total: {{ $summary['total'] }}</div>
            <div class="badge bg-yellow-lt fs-5">&gt; 30 hari: {{ $summary['over_30'] }}</div>
            <div class="badge bg-orange-lt fs-5">&gt; 60 hari: {{ $summary['over_60'] }}</div>
            <div class="badge bg-red-lt fs-5">&gt; 90 hari: {{ $summary['over_90'] }}</div>
        </div>

        <div class="card">
            {{-- Filter Bar --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('customers.follow-up.index') }}">
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label class="form-label">Cari Pelanggan</label>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Nama atau telepon..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tidak Order Lebih Dari</label>
                            <select name="min_days" class="form-select">
                                <option value="">Semua</option>
                                <option value="30" {{ request('min_days') == '30' ? 'selected' : '' }}>30 hari</option>
                                <option value="60" {{ request('min_days') == '60' ? 'selected' : '' }}>60 hari</option>
                                <option value="90" {{ request('min_days') == '90' ? 'selected' : '' }}>90 hari</option>
                                <option value="180" {{ request('min_days') == '180' ? 'selected' : '' }}>180 hari</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table card-table table-vcenter text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Telepon</th>
                                <th>Area</th>
                                <th>Order Terakhir</th>
                                <th>Days</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $index => $customer)
                                @php
                                    $days = (int) ceil(\Carbon\Carbon::parse($customer->last_order_date)->diffInDays(now()));
                                    $area = $customer->addresses->first()?->area->name ?? 'N/A';

                                    // Badge color based on days
                                    if ($days >= 90) {
                                        $badgeClass = 'bg-red text-white fw-bold';
                                    } elseif ($days >= 60) {
                                        $badgeClass = 'bg-orange text-white';
                                    } elseif ($days >= 30) {
                                        $badgeClass = 'bg-yellow text-dark';
                                    } else {
                                        $badgeClass = 'text-secondary';
                                    }

                                    // WhatsApp phone formatting
                                    $rawPhone = preg_replace('/[^0-9]/', '', $customer->phone_number);
                                    if (str_starts_with($rawPhone, '0')) {
                                        $rawPhone = '62' . substr($rawPhone, 1);
                                    } elseif (!str_starts_with($rawPhone, '62')) {
                                        $rawPhone = '62' . $rawPhone;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $customers->firstItem() + $index }}</td>
                                    <td>
                                        <a href="{{ route('web.customers.show', $customer->id) }}" class="text-primary fw-medium">
                                            {{ $customer->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="phone-copy cursor-pointer text-primary"
                                              data-phone="{{ $customer->phone_number }}"
                                              title="Klik untuk menyalin">
                                            {{ $customer->phone_number }}
                                        </span>
                                    </td>
                                    <td>{{ $area }}</td>
                                    <td>{{ \Carbon\Carbon::parse($customer->last_order_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $days }} hari
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-list">
                                            <a href="https://wa.me/{{ $rawPhone }}" target="_blank"
                                               class="btn btn-sm btn-success"
                                               title="WhatsApp">
                                                <i class="ti ti-brand-whatsapp"></i> WhatsApp
                                            </a>
                                            <a href="{{ route('web.customers.show', $customer->id) }}"
                                               class="btn btn-sm btn-secondary"
                                               title="Detail">
                                                <i class="ti ti-eye"></i> Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Tidak ada data pelanggan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if($customers->hasPages())
                <div class="card-footer">
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Phone copy on click
    document.querySelectorAll('.phone-copy').forEach(function (el) {
        el.addEventListener('click', function () {
            const phone = this.dataset.phone;
            navigator.clipboard.writeText(phone).then(function () {
                const original = el.textContent;
                el.textContent = '✓ Disalin!';
                el.classList.remove('text-primary');
                el.classList.add('text-success');
                setTimeout(function () {
                    el.textContent = original;
                    el.classList.remove('text-success');
                    el.classList.add('text-primary');
                }, 1500);
            });
        });
    });
});
</script>
@endpush
