@extends('layouts.admin')
@section('title', 'Laporan Profitabilitas')

@section('content')
<div class="container-xl" id="profitability-report-page">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Profitabilitas</h2>
                <div class="text-muted mt-1">Analisis keuntungan berdasarkan layanan dan area.</div>
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
                <h3 class="card-title">Profitabilitas per Layanan</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="profit-by-service-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.reports.profitability.services') }}">
                        <thead>
                            <tr>
                                <th>Nama Layanan</th>
                                <th>Total Pendapatan</th>
                                <th>Total Biaya</th>
                                <th>Total Keuntungan</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Profit by Area Chart --}}
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Profitabilitas per Area</h3>
            </div>
            <div class="card-body">
                <div id="chart-profit-by-area" data-url="{{ route('data.reports.profitability.areas') }}"></div>
            </div>
        </div>
    </div>
</div>
@endsection