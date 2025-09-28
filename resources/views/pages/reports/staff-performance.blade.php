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
