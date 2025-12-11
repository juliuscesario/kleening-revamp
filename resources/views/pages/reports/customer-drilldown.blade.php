@extends('layouts.admin')
@section('title', 'Profil Pelanggan: ' . $customer->name)

@section('content')
<div class="container-xl" id="customer-drilldown-page"
    data-customer-id="{{ $customer->id }}"
    data-customer-name="{{ $customer->name }}"
    data-timeline-url="{{ route('data.reports.customer.spending-timeline', $customer) }}"
    data-metrics-url="{{ route('data.reports.customer.key-metrics', $customer) }}"
    data-frequency-url="{{ route('data.reports.customer.service-frequency', $customer) }}"
    data-history-url="{{ route('data.reports.customer.order-history', $customer) }}">

    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Profil Analisis Pelanggan</h2>
                <div class="text-muted mt-1">Analisis mendalam untuk <span class="fw-bold">{{ $customer->name }}</span></div>
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
        {{-- Key Metrics Cards --}}
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="font-weight-medium" id="metric-total-spent">Loading...</div>
                        <div class="text-muted">Total Belanja (Paid)</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="font-weight-medium" id="metric-total-orders">Loading...</div>
                        <div class="text-muted">Total Pesanan</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="font-weight-medium" id="metric-avg-days">Loading...</div>
                        <div class="text-muted">Rata-rata Jarak Pesanan</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="font-weight-medium" id="metric-frequent-service">Loading...</div>
                        <div class="text-muted">Layanan Favorit</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row-cards">
            {{-- Spending Timeline --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">Linimasa Belanja (per Bulan)</div>
                    <div class="card-body">
                        <div id="chart-spending-timeline"></div>
                    </div>
                </div>
            </div>

            {{-- Service Frequency --}}
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">Frekuensi Pemesanan Layanan</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="service-frequency-table" class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>Nama Layanan</th>
                                        <th>Jumlah Dipesan</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order History --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Riwayat Semua Pesanan</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="order-history-table" class="table card-table table-vcenter text-nowrap datatable">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Tgl. Kerja</th>
                                        <th>Area</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
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
