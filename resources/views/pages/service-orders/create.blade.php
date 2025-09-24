@extends('layouts.admin')
@section('title', 'Buat Service Order Baru')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Buat Service Order Baru</h2>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div id="stepper" class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                    <li class="nav-item">
                        <a href="#step-1" class="nav-link active" data-bs-toggle="tab">Langkah 1: Pilih Customer</a>
                    </li>
                    <li class="nav-item">
                        <a href="#step-2" class="nav-link" data-bs-toggle="tab">Langkah 2: Detail Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a href="#step-3" class="nav-link" data-bs-toggle="tab">Langkah 3: Konfirmasi</a>
                    </li>
                </ul>
            </div>
            <form id="create-so-form" action="{{ route('web.service-orders.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="step-1">
                            <h4>Pilih Customer</h4>
                            <div class="mb-3">
                                <label class="form-label">Customer</label>
                                <select id="customer-select" name="customer_id" class="form-select" required>
                                    <option value="">Pilih Customer</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-primary" id="next-to-step-2">Selanjutnya</button>
                            </div>
                        </div>
                        <div class="tab-pane" id="step-2">
                            <h4>Detail Pesanan</h4>
                            <input type="hidden" name="address_id" id="address_id">
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <select id="address-select" class="form-select" required>
                                    <option value="">Pilih Alamat</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Area</label>
                                <input type="text" id="area-name" class="form-control" readonly>
                                <input type="hidden" id="area-id" name="area_id">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Pengerjaan</label>
                                <input type="date" name="work_date" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pilih Layanan</label>
                                <select name="services[]" id="services-select" class="form-select" multiple required>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pilih Staff</label>
                                <select name="staff[]" id="staff-select" class="form-select" multiple>
                                </select>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Catatan untuk Dikerjakan</label>
                                <textarea name="work_notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan untuk Staff (Internal)</label>
                                <textarea name="staff_notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary" id="back-to-step-1">Kembali</button>
                                <button type="button" class="btn btn-primary" id="next-to-step-3">Selanjutnya</button>
                            </div>
                        </div>
                        <div class="tab-pane" id="step-3">
                            <h4>Konfirmasi Pesanan</h4>
                            <div id="confirmation-details"></div>
                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-secondary" id="back-to-step-2">Kembali</button>
                                <button type="submit" class="btn btn-primary">Buat Service Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/pages/service-orders-create.js') }}"></script>
@endpush