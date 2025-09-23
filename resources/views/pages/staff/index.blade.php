@extends('layouts.admin')
@section('title', 'Manajemen Staff')

{{-- Content section for the page --}}
@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            {{-- Page title for Staff --}}
            <div class="col">
                <h2 class="page-title">Manajemen Staff</h2>
                {{-- Subtitle for the Staff page --}}
                <div class="text-muted mt-1">Daftar semua staff.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" id="add-staff-button" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Tambah Staff Baru
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
                    {{-- DataTable for displaying staff --}}
                    <table id="staff-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.staff') }}"
                        data-api-url="{{ url('api/staff') }}">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>No. Telepon</th>
                                <th>Role</th>
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

{{-- Modal for adding/editing a staff member --}}
<div class="modal modal-blur fade" id="modal-staff" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{-- Form for staff data submission --}}
            <form id="staff-form">
                <div class="modal-header">
                    {{-- Modal title, dynamically set to "Tambah Staff" or "Edit Staff" --}}
                    <h5 class="modal-title" id="modal-title">Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Hidden input for staff ID (used for editing) --}}
                    <input type="hidden" name="id" id="staff-id">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" id="staff-name" placeholder="Contoh: John Doe">
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" name="phone_number" id="staff-phone_number" placeholder="Contoh: 08123456789">
                        <div class="invalid-feedback" id="phone_number-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="staff-password" placeholder="Isi untuk mengubah password">
                        <div class="invalid-feedback" id="password-error"></div>
                        <small class="form-hint">Kosongkan jika tidak ingin mengubah password saat edit.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area</label>
                        <select class="form-select" name="area_id" id="staff-area_id">
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="area_id-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="staff-role">
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="invalid-feedback" id="role-error"></div>
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