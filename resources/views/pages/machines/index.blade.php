@extends('layouts.admin')
@section('title', 'Manajemen Mesin')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Manajemen Mesin</h2>
                <div class="text-muted mt-1">Daftar semua mesin per area.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" id="add-machine-button" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Tambah Mesin
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-header">
                <div class="row g-2">
                    <div class="col-auto">
                        <select id="filter-category" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <select id="filter-area" class="form-select">
                            <option value="">Semua Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <select id="filter-status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="retired">Retired</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="machines-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.machines') }}"
                        data-api-url="{{ url('api/machines') }}">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Kategori</th>
                                <th>Area</th>
                                <th>Status</th>
                                <th>Mesin Pasangan</th>
                                <th>Catatan</th>
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

<div class="modal modal-blur fade" id="modal-machine" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form id="machine-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="machine-id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="machine-category-id" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="category_id-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Area <span class="text-danger">*</span></label>
                                <select class="form-select" name="area_id" id="machine-area-id" required>
                                    <option value="">Pilih Area</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="area_id-error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Kode <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="code" id="machine-code" required>
                                    <button type="button" class="btn btn-outline-secondary" id="suggest-code-btn">Suggest</button>
                                </div>
                                <div class="invalid-feedback" id="code-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama (Opsional)</label>
                                <input type="text" class="form-control" name="name" id="machine-name">
                                <div class="invalid-feedback" id="name-error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="machine-status" required>
                                    <option value="active">Active</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="retired">Retired</option>
                                </select>
                                <div class="invalid-feedback" id="status-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="paired-machine-wrapper">
                                <label class="form-label">Mesin Pasangan</label>
                                <select class="form-select" name="paired_machine_id" id="machine-paired-machine-id">
                                    <option value="">Tidak ada</option>
                                </select>
                                <div class="invalid-feedback" id="paired_machine_id-error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" name="notes" id="machine-notes" rows="2"></textarea>
                        <div class="invalid-feedback" id="notes-error"></div>
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
@endsection
