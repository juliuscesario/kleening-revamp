@extends('layouts.admin')
@section('title', 'Drilldown Pendapatan: ' . $serviceCategory->name)

@push('styles')
<style>
    .card-header {
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="container-xl" id="revenue-drilldown-page"
    data-category-id="{{ $serviceCategory->id }}"
    data-category-name="{{ $serviceCategory->name }}"
    data-trend-url="{{ route('data.reports.revenue.trend', $serviceCategory) }}"
    data-area-url="{{ route('data.reports.revenue.area', $serviceCategory) }}"
    data-table-url="{{ route('data.reports.revenue.table', $serviceCategory) }}">

    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Pendapatan: Drilldown</h2>
                <div class="text-muted mt-1">Analisis detail untuk kategori <span class="fw-bold">{{ $serviceCategory->name }}</span></div>
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
                    Menampilkan data untuk periode <strong id="filter-display-date"></strong> 
                    @if(request()->get('area_id') && request()->get('area_id') !== 'all')
                        dan area <strong id="filter-display-area"></strong>.
                    @else
                        (semua area).
                    @endif
                </div>
            </div>
        </div>

        <div class="row row-cards">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">Tren Pendapatan Harian</div>
                    <div class="card-body">
                        <div id="chart-revenue-trend"></div>
                    </div>
                </div>
            </div>
            @if(auth()->user()->role === 'owner')
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">Distribusi Pendapatan per Area</div>
                    <div class="card-body">
                        <div id="chart-revenue-area"></div>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Detail Item Pesanan</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="revenue-drilldown-table" class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Customer</th>
                                        <th>Tgl. Kerja</th>
                                        <th>Layanan</th>
                                        <th>Total</th>
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
