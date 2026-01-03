@extends('layouts.admin')
@section('title', 'Manajemen Customer')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Manajemen Customer</h2>
                <div class="text-muted mt-1">Daftar semua customer.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @can('create', \App\Models\Customer::class)
                <div class="btn-list">
                    <a href="#" id="add-customer-button" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Tambah Customer Baru
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="customers-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.customers') }}"
                        data-api-url="{{ url('api/customers') }}">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>No. Telepon</th>
                                <th>Jumlah Alamat</th>
                                <th>Tgl. Daftar</th>
                                <th>Order Terakhir</th>
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

{{-- Modal for adding/editing a customer --}}
<div class="modal modal-blur fade" id="modal-customer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="customer-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="customer-id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Customer</label>
                                <input type="text" class="form-control" name="name" id="customer-name" placeholder="Contoh: Budi Santoso">
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" class="form-control" name="phone_number" id="customer-phone_number" placeholder="Contoh: 0812...">
                                <div class="invalid-feedback" id="phone_number-error"></div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    {{-- Optional Address Section --}}
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="add-address-checkbox" name="add_address" value="1">
                        <label class="form-check-label" for="add-address-checkbox">Tambah Alamat Baru</label>
                    </div>
                    <div id="address-fields" class="mt-3" style="display: none;">
                        <h5>Detail Alamat</h5>
                        <div class="row">
                            <div class="col-md-6">
                                @if(in_array(Auth::user()->role, ['owner', 'admin']))
                                <div class="mb-3">
                                    <label class="form-label">Area</label>
                                    <select class="form-select" name="area_id" id="address-area_id">
                                        @foreach($areas as $area)
                                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="area_id-error"></div>
                                </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">Label Alamat</label>
                                    <input type="text" class="form-control" name="label" id="address-label" placeholder="Contoh: Rumah, Kantor">
                                    <div class="invalid-feedback" id="label-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="copy-customer-data">
                                        <span class="form-check-label">Samakan dengan Data Customer</span>
                                    </label>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Kontak</label>
                                    <input type="text" class="form-control" name="contact_name" id="address-contact_name" placeholder="Nama penerima di alamat">
                                    <div class="invalid-feedback" id="contact_name-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon Kontak</label>
                                    <input type="text" class="form-control" name="contact_phone" id="address-contact_phone">
                                    <div class="invalid-feedback" id="contact_phone-error"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control" name="full_address" id="address-full_address" rows="5"></textarea>
                                    <div class="invalid-feedback" id="full_address-error"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Link Google Maps (Opsional)</label>
                                    <input type="url" class="form-control" name="google_maps_link" id="address-google_maps_link">
                                    <div class="invalid-feedback" id="google_maps_link-error"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit-button">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal for showing addresses --}}
<div class="modal modal-blur fade" id="modal-show-addresses" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="show-addresses-title">Daftar Alamat Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="address-list-container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
