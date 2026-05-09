@extends('layouts.admin')
@section('title', 'Manajemen Kategori Mesin')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Manajemen Kategori Mesin</h2>
                <div class="text-muted mt-1">Daftar semua kategori mesin.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" id="add-category-button" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Tambah Kategori
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="machine-categories-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.machine-categories') }}"
                        data-api-url="{{ url('api/machine-categories') }}">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Kategori</th>
                                <th>Prefix Kode</th>
                                <th>Urutan</th>
                                <th>Status</th>
                                <th>Jumlah Mesin</th>
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

<div class="modal modal-blur fade" id="modal-machine-category" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="machine-category-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Kategori Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="machine-category-id">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" name="name" id="machine-category-name" placeholder="Contoh: Hydrovacuum">
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prefix Kode</label>
                        <input type="text" class="form-control" name="code_prefix" id="machine-category-code-prefix" placeholder="hv, s, pw, gc">
                        <div class="invalid-feedback" id="code_prefix-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control" name="sort_order" id="machine-category-sort-order" value="0">
                        <div class="invalid-feedback" id="sort_order-error"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="machine-category-is-active" value="1" checked>
                            <label class="form-check-label" for="machine-category-is-active">Aktif</label>
                        </div>
                        <div class="invalid-feedback" id="is_active-error"></div>
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
