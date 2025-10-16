@extends('layouts.admin')
@section('title', 'Laporan Pertumbuhan Pelanggan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Pertumbuhan Pelanggan</h2>
                <div class="text-muted mt-1">Analisis pelanggan baru dan pelanggan teratas.</div>
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

        {{-- Data Table --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Pelanggan Teratas (Berdasarkan Pendapatan)</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="customer-growth-report-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.reports.customer-growth') }}"
                        data-drilldown-url="{{ route('web.reports.customer.drilldown', ['customer' => '__ID__']) }}">
                        <thead>
                            <tr>
                                <th>Nama Pelanggan</th>
                                <th>Total Pesanan</th>
                                <th>Total Pendapatan</th>
                                <th>Total Cancelled Revenue Potential</th>
                                <th>Total Invoice Overdue</th>
                                <th>Total Invoice Unpaid</th>
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
