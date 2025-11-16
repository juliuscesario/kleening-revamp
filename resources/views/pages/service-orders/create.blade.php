@extends('layouts.admin')
@section('title', 'Buat Service Order Baru')

@push('styles')
<style>
    .custom-search-wrapper {
        position: relative;
    }
    .custom-search-results {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 .25rem .25rem;
        max-height: 250px;
        overflow-y: auto;
        background-color: #fff;
        z-index: 1050; /* High z-index to appear above other elements */
    }
    .custom-search-results.is-active {
        display: block;
    }
    .result-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
    }
    .result-item:last-child {
        border-bottom: none;
    }
    .result-item:hover {
        background-color: #f8f9fa;
    }
    .result-item.is-highlighted {
        background-color: #0d6efd;
        color: #fff;
    }
</style>
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
        <div class="card">
            <form id="create-so-form" 
                method="POST" 
                action="{{ route('web.service-orders.store') }}"
                data-customers-url="{{ route('data.customers') }}"
                data-addresses-url-template="{{ route('data.customers.addresses', ['customer' => '__CUSTOMER_ID__']) }}"
                data-services-url="{{ route('data.services') }}"
                data-staff-by-area-url-template="{{ route('data.staff.by-area', ['area' => '__AREA_ID__']) }}"
            >
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer</label>
                            <div class="custom-search-wrapper">
                                <input type="text" id="customer-search" class="form-control" placeholder="Cari nama customer..." autocomplete="off">
                                <input type="hidden" name="customer_id" id="customer_id" required>
                                <div id="customer-results" class="custom-search-results"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alamat</label>
                            <select id="address-select" name="address_id" class="form-select" required disabled>
                                <option value="">Pilih Customer terlebih dahulu</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area</label>
                            <input type="text" id="area-name" class="form-control" readonly>
                            <input type="hidden" id="area-id" name="area_id">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Pengerjaan</label>
                            <input type="date" name="work_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Pengerjaan (WIB)</label>
                            <input type="text"
                                name="work_time"
                                class="form-control js-work-time-input"
                                inputmode="numeric"
                                pattern="^([01]\d|2[0-3]):[0-5]\d$"
                                placeholder="00:00"
                                required
                                value="{{ old('work_time', now()->setTimezone('Asia/Jakarta')->format('H:i')) }}">
                            <small class="form-text text-muted">Masukkan 24 jam (00:00 - 23:59), contoh: 07:30 atau 16:45.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Invoice</label>
                        <textarea name="work_notes" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan untuk Staff (Internal)</label>
                        <textarea name="staff_notes" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ordered Services</label>
                        <div id="service-items-container">
                            {{-- Items will be added here dynamically --}}
                        </div>
                        <button type="button" class="btn btn-success mt-2 w-100" id="add-service-item">Add Service</button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned Staff</label>
                        <div id="staff-container">
                            {{-- Staff will be added here dynamically --}}
                        </div>
                        <button type="button" class="btn btn-success mt-2 w-100" id="add-staff-member" disabled>Pilih Alamat terlebih dahulu</button>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Buat Service Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
