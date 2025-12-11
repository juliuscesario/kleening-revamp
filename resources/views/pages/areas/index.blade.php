@extends('layouts.admin')
@section('title', 'Manajemen Area')

{{-- Content section for the page --}}
@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            {{-- Page title --}}
            <div class="col">
                <h2 class="page-title">Manajemen Area</h2>
                {{-- Subtitle for the page --}}
                <div class="text-muted mt-1">Daftar semua area operasional.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" id="add-area-button" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Tambah Area Baru
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
                    <table id="areas-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.areas') }}"
                        data-api-url="{{ url('api/areas') }}">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Area</th>
                                <th>Dibuat Pada</th>
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

{{-- Modal for adding/editing an area --}}
<div class="modal modal-blur fade" id="modal-area" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{-- Form for area data submission --}}
            <form id="area-form">
                <div class="modal-header">
                    {{-- Modal title, dynamically set to "Tambah Area" or "Edit Area" --}}
                    <h5 class="modal-title" id="modal-title">Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Hidden input for area ID (used for editing) --}}
                    <input type="hidden" name="id" id="area-id">
                    <div class="mb-3">
                        {{-- Label for area name input --}}
                        <label class="form-label">Nama Area</label>
                        {{-- Input field for area name --}}
                        <input type="text" class="form-control" name="name" id="area-name" placeholder="Contoh: Jakarta Selatan">
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