@extends('layouts.admin')
@section('title', 'Manajemen Layanan')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Manajemen Layanan</h2>
                <div class="text-muted mt-1">Daftar semua layanan yang ditawarkan.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" id="add-service-button" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Tambah Layanan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="services-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.services') }}"
                        data-api-url="{{ url('api/services') }}">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Layanan</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                @if(in_array(auth()->user()->role, ['owner', 'co_owner']))
                                <th>Biaya (Cost)</th>
                                @endif
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

{{-- Modal for adding/editing a service --}}
<div class="modal modal-blur fade" id="modal-service" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="service-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Layanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="service-id">
                    <div class="mb-3">
                        <label class="form-label">Nama Layanan</label>
                        <input type="text" class="form-control" name="name" id="service-name" placeholder="Contoh: Cuci Setrika Kiloan">
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category_id" id="service-category_id">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="category_id-error"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="price" id="service-price" placeholder="Contoh: 10000">
                        <div class="invalid-feedback" id="price-error"></div>
                    </div>
                    @if(in_array(auth()->user()->role, ['owner', 'co_owner']))
                    <div class="mb-3">
                        <label class="form-label">Biaya (Cost)</label>
                        <input type="number" class="form-control" name="cost" id="service-cost" placeholder="Contoh: 5000">
                        <div class="invalid-feedback" id="cost-error"></div>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" id="service-description" rows="3"></textarea>
                        <div class="invalid-feedback" id="description-error"></div>
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
