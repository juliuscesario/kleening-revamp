@extends('layouts.admin')
@section('title', 'Drilldown Kinerja: ' . $staff->name)

@section('content')
<div class="container-xl" id="staff-drilldown-page"
    data-staff-id="{{ $staff->id }}"
    data-staff-name="{{ $staff->name }}"
    data-workload-url="{{ route('data.reports.staff.workload', $staff) }}"
    data-specialization-url="{{ route('data.reports.staff.specialization', $staff) }}"
    data-table-url="{{ route('data.reports.staff.table', $staff) }}">

    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Kinerja Staff: Drilldown</h2>
                <div class="text-muted mt-1">Analisis detail untuk staff <span class="fw-bold">{{ $staff->name }}</span></div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ url()->previous() }}" class="btn btn-outline-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l4 4"></path><path d="M5 12l-4 -4"></path></svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="page-body">
        {{-- Filter Info --}}
        <div class="alert alert-info" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M12 9h.01"></path><path d="M11 12h1v4h1"></path></svg>
                </div>
                <div>
                    Menampilkan data untuk periode <strong id="filter-display-date"></strong>.
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Beban Kerja Mingguan (Jumlah Pekerjaan)</div>
                    <div class="card-body">
                        <div id="chart-staff-workload"></div>
                    </div>
                </div>
            </div>
             <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Spesialisasi Layanan</div>
                    <div class="card-body">
                        <div id="chart-staff-specialization"></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Detail Pekerjaan yang Ditangani</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="staff-drilldown-table" class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Customer</th>
                                        <th>Tgl. Kerja</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
