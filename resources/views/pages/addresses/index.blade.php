@extends('layouts.admin')
@section('title', 'Manajemen Alamat')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Manajemen Alamat</h2>
                <div class="text-muted mt-1">Daftar semua alamat customer.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="addresses-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.addresses') }}"
                        data-api-url="{{ url('api/addresses') }}">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Label</th>
                                <th>Customer</th>
                                <th>Alamat Lengkap</th>
                                <th>Kontak</th>
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
