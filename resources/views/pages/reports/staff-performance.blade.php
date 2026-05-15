@extends('layouts.admin')
@section('title', 'Laporan Kinerja Staff')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Kinerja Staff</h2>
                <div class="text-muted mt-1">Analisis kinerja staff berdasarkan periode dan area.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        {{-- Periode Shortcuts --}}
        <div class="card mb-2">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <select id="shortcut-month" class="form-select form-select-sm" style="width:120px">
                        <option value="0">Januari</option>
                        <option value="1">Februari</option>
                        <option value="2">Maret</option>
                        <option value="3">April</option>
                        <option value="4">Mei</option>
                        <option value="5">Juni</option>
                        <option value="6">Juli</option>
                        <option value="7">Agustus</option>
                        <option value="8">September</option>
                        <option value="9">Oktober</option>
                        <option value="10">November</option>
                        <option value="11">Desember</option>
                    </select>
                    <select id="shortcut-year" class="form-select form-select-sm" style="width:90px">
                        @for($y = 2024; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                    <span class="text-muted mx-1" style="font-size:13px">|</span>
                    <button type="button" class="btn btn-sm periode-btn" data-periode="1">Periode 1</button>
                    <button type="button" class="btn btn-sm periode-btn" data-periode="2">Periode 2</button>
                    <button type="button" class="btn btn-sm periode-btn" data-periode="3">Periode 3</button>
                    <span id="periode-desc" class="text-muted ms-2" style="font-size:12px"></span>
                </div>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Periode Tanggal</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="filter-start-date">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" id="filter-end-date">
                        </div>
                    </div>
                    @if(auth()->user()->role === 'owner')
                    <div class="col-md-3">
                        <label class="form-label">Area</label>
                        <select class="form-select" id="filter-area">
                            <option value="all">Semua Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3">
                        <label class="form-label">Staff</label>
                        <select class="form-select" id="filter-staff">
                            <option value="all">Semua Staff</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="apply-filters">Terapkan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="staff-performance-report-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.reports.staff-performance') }}"
                        data-drilldown-url="{{ route('web.reports.staff.drilldown', ['staff' => '__ID__']) }}">
                        <thead>
                            <tr>
                                <th>Nama Staff</th>
                                <th>Area</th>
                                <th>Pekerjaan Selesai</th>
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
