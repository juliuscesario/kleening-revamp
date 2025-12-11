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
                                <th>Area</th>
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

{{-- Modal for adding/editing an address --}}
<div class="modal modal-blur fade" id="modal-address" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{-- Form for address data submission --}}
            <form id="address-form">
                <div class="modal-header">
                    {{-- Modal title, dynamically set to "Tambah Alamat" or "Edit Alamat" --}}
                    <h5 class="modal-title" id="modal-title">Alamat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Hidden input for address ID (used for editing) --}}
                    <input type="hidden" name="id" id="address-id">
                    <div class="mb-3">
                        <label class="form-label">Label</label>
                        <input type="text" class="form-control" name="label" id="address-label" placeholder="Contoh: Rumah, Kantor">
                        <div class="invalid-feedback" id="label-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Kontak</label>
                        <input type="text" class="form-control" name="contact_name" id="address-contact-name" placeholder="Contoh: John Doe">
                        <div class="invalid-feedback" id="contact-name-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telepon Kontak</label>
                        <input type="text" class="form-control" name="contact_phone" id="address-contact-phone" placeholder="Contoh: 081234567890">
                        <div class="invalid-feedback" id="contact-phone-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" name="full_address" id="address-full-address" rows="3"></textarea>
                        <div class="invalid-feedback" id="full-address-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link Google Maps</label>
                        <input type="text" class="form-control" name="google_maps_link" id="address-google-maps-link" placeholder="Contoh: https://maps.app.goo.gl/xxxx">
                        <div class="invalid-feedback" id="google-maps-link-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- Buttons for closing modal and submitting form --}}
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit-button">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>