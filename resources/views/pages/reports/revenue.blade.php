@extends('layouts.admin')
@section('title', 'Laporan Pendapatan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Pendapatan</h2>
                <div class="text-muted mt-1">Analisis pendapatan berdasarkan periode dan area.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        {{-- Filter Section --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Periode Tanggal</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="filter-start-date">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" id="filter-end-date">
                        </div>
                    </div>
                    @if(auth()->user()->role === 'owner')
                    <div class="col-md-5">
                        <label class="form-label">Area</label>
                        <select class="form-select" id="filter-area">
                            <option value="all">Semua Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="apply-filters">Terapkan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-green text-white avatar">$</span></div>
                            <div class="col">
                                <div class="font-weight-medium" id="summary-total-revenue">Rp 0</div>
                                <div class="text-muted">Total Pendapatan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-blue text-white avatar">#</span></div>
                            <div class="col">
                                <div class="font-weight-medium" id="summary-total-orders">0</div>
                                <div class="text-muted">Total Pesanan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-yellow text-white avatar">%</span></div>
                            <div class="col">
                                <div class="font-weight-medium" id="summary-avg-revenue">Rp 0</div>
                                <div class="text-muted">Pendapatan Rata-rata</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="revenue-report-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.reports.revenue') }}">
                        <thead>
                            <tr>
                                <th>Kategori Layanan</th>
                                <th>Jumlah Pesanan</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
