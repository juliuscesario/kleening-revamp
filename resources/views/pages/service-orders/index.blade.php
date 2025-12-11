@extends('layouts.admin')
@section('title', 'Daftar Service Order')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Daftar Service Order @if(isset($serviceOrders) && $serviceOrders->isNotEmpty()) (Belum Ada Staff) @endif</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @if(!isset($serviceOrders))
                <a href="{{ route('web.service-orders.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Tambah SO Baru
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="page-body">
        @if (isset($serviceOrders))
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Service Orders Belum Ada Staff</h3>
                </div>
                <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                    @if($serviceOrders->isEmpty())
                        <p>Tidak ada Service Order yang belum memiliki staff.</p>
                    @else
                        <div class="list-group">
                            @foreach($serviceOrders as $so)
                                <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <h5 class="mb-1">{{ $so->so_number }}</h5>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark">{{ $so->work_date->format('d M Y') }}</div>
                                            @if($so->work_time_formatted)
                                                <div class="text-primary fw-semibold">{{ $so->work_time_formatted }} WIB</div>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="mb-1">Pelanggan: {{ $so->customer->name }}</p>
                                    <small>Staff: {{ $so->staff->pluck('name')->join(', ') ?: 'Belum ada' }}</small>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header">
                    <div class="row g-3 w-100 align-items-start">
                        <div class="col-lg-7">
                            <p class="mb-2 fw-bold">Filter Jadwal</p>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" id="filter-start-date" class="form-control form-control-sm js-filter-date" placeholder="Mulai (dd/mm/yyyy)" inputmode="numeric">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" id="filter-start-time" class="form-control form-control-sm js-filter-time" placeholder="Mulai (00:00)" inputmode="numeric">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" id="filter-end-date" class="form-control form-control-sm js-filter-date" placeholder="Sampai (dd/mm/yyyy)" inputmode="numeric">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" id="filter-end-time" class="form-control form-control-sm js-filter-time" placeholder="Sampai (23:59)" inputmode="numeric">
                                </div>
                            </div>
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary btn-sm" id="apply-date-filter">Terapkan</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-date-filter">Reset</button>
                                <small class="text-muted align-self-center">Filter berdasarkan tanggal & waktu pengerjaan.</small>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <p class="mb-2 fw-bold">Filter berdasar status</p>
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-outline-primary btn-sm filter-status-btn" data-status="all">All</button>
                                    <button class="btn btn-outline-info btn-sm filter-status-btn" data-status="booked">Booked</button>
                                    <button class="btn btn-outline-warning btn-sm filter-status-btn" data-status="proses">Proses</button>
                                    <button class="btn btn-outline-success btn-sm filter-status-btn" data-status="done">Done</button>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-outline-danger btn-sm filter-status-btn" data-status="cancelled">Cancelled</button>
                                    <button class="btn btn-outline-secondary btn-sm filter-status-btn" data-status="invoiced">Invoiced</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="service-orders-table" class="table card-table table-vcenter text-nowrap datatable"
                            data-url="{{ route('data.service-orders') }}"
                            data-update-url-template="{{ route('web.service-orders.update', ['service_order' => '__SERVICE_ORDER_ID__']) }}">
                            <thead>
                                <tr>
                                    <th>SO Number</th>
                                    <th>Customer</th>
                                    <th>Nomor HP</th>
                                    <th>Tanggal & Waktu (WIB)</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
