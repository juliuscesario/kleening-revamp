@extends('layouts.admin')
@section('title', 'Manajemen Kategori Layanan')

{{-- Content section for the page --}}
@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            {{-- Page title for Service Categories --}}
            <div class="col">
                <h2 class="page-title">Manajemen Kategori Layanan</h2>
                {{-- Subtitle for the Service Categories page --}}
                <div class="text-muted mt-1">Daftar semua kategori layanan.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" id="add-service-category-button" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Tambah Kategori Layanan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Page body containing the card with the table --}}
    <div class="page-body">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    {{-- DataTable for displaying areas --}}
                    <table id="service-categories-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.service-categories') }}"
                        data-api-url="{{ url('api/service-categories') }}">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Kategori Layanan</th>
                                <th>Dibuat Tanggal</th>
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

{{-- Modal for adding/editing a service category --}}
<div class="modal modal-blur fade" id="modal-service-category" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{-- Form for service category data submission --}}
            <form id="service-category-form">
                <div class="modal-header">
                    {{-- Modal title, dynamically set to "Tambah Kategori Layanan" or "Edit Kategori Layanan" --}}
                    <h5 class="modal-title" id="modal-title">Kategori Layanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Hidden input for service category ID (used for editing) --}}
                    <input type="hidden" name="id" id="service-category-id">
                    <div class="mb-3">
                        {{-- Label for service category name input --}}
                        <label class="form-label">Nama Kategori Layanan</label>
                        {{-- Input field for service category name --}}
                        <input type="text" class="form-control" name="name" id="service-category-name" placeholder="Contoh: General Cleaning">
                        {{-- Feedback for validation errors --}}
                        <div class="invalid-feedback" id="name-error"></div>
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
@endsection