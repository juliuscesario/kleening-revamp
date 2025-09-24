@extends('layouts.admin')
@section('title', 'Daftar Service Order')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Daftar Service Order</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('web.customers.index') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    Tambah SO Baru
                </a>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Status</h3>
                <div class="card-actions">
                    <button class="btn btn-outline-primary filter-status-btn" data-status="all">All</button>
                    <button class="btn btn-outline-info filter-status-btn" data-status="booked">Booked</button>
                    <button class="btn btn-outline-warning filter-status-btn" data-status="proses">Proses</button>
                    <button class="btn btn-outline-success filter-status-btn" data-status="done">Done</button>
                    <button class="btn btn-outline-danger filter-status-btn" data-status="cancelled">Cancelled</button>
                    <button class="btn btn-outline-secondary filter-status-btn" data-status="invoiced">Invoiced</button>
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
                                <th>Tanggal Kerja</th>
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
@endsection
