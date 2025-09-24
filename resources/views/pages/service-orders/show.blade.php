@extends('layouts.admin')
@section('title', 'Detail Service Order')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Detail Service Order: {{ $serviceOrder->so_number }}</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('web.service-orders.index') }}" class="btn">Kembali</a>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detail Pesanan</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Customer:</strong>
                            @if ($serviceOrder->customer)
                                {{ $serviceOrder->customer->name }}
                                @if ($serviceOrder->customer->trashed())
                                    <span class="badge bg-danger text-bg-secondary">Archived</span>
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                        <p><strong>Alamat:</strong> {{ $serviceOrder->address->full_address }}</p>
                        <p><strong>Area:</strong> {{ $serviceOrder->address->area->name }}</p>
                        <p><strong>Tanggal Pengerjaan:</strong> {{ \Carbon\Carbon::parse($serviceOrder->work_date)->format('d M Y') }}</p>
                        <p><strong>Status:</strong> <span class="badge bg-secondary">{{ $serviceOrder->status }}</span></p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Layanan yang Dipesan</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap">
                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th class="text-end">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceOrder->items as $item)
                                <tr>
                                    <td>{{ $item->service->name }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Rp {{ number_format($serviceOrder->items->sum('price'), 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Staff yang Bertugas</h3>
                    </div>
                    <div class="card-body">
                        @if($serviceOrder->staff->isNotEmpty())
                            <ul class="list-group list-group-flush">
                                @foreach($serviceOrder->staff as $staff)
                                    <li class="list-group-item">{{ $staff->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Belum ada staff yang ditugaskan.</p>
                        @endif
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Catatan</h3>
                    </div>
                    <div class="card-body">
                        <h5>Catatan untuk Dikerjakan</h5>
                        <p class="text-muted">{{ $serviceOrder->work_notes ?? 'Tidak ada catatan.' }}</p>
                        <h5 class="mt-3">Catatan Internal (untuk Staff)</h5>
                        <p class="text-muted">{{ $serviceOrder->staff_notes ?? 'Tidak ada catatan.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
