@extends('layouts.admin')
@section('title', 'Buat Service Order Baru')

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
            <div class="card-header">
                <h3 class="card-title">Detail Pesanan</h3>
            </div>
            <div class="card-body">
                <p><strong>Customer:</strong> {{ $address->customer->name }}</p>
                <p><strong>Alamat:</strong> {{ $address->full_address }}</p>
                <p><strong>Area:</strong> {{ $address->area->name }}</p>
            </div>
        </div>

        <form action="{{ route('web.service-orders.store') }}" method="POST" class="card mt-4">
            @csrf
            <input type="hidden" name="address_id" value="{{ $address->id }}">
            <input type="hidden" name="customer_id" value="{{ $address->customer_id }}">

            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Tanggal Pengerjaan</label>
                    <input type="date" name="work_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pilih Layanan</label>
                    <select name="services[]" class="form-select" multiple required>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }} (Rp {{ number_format($service->price, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pilih Staff</label>
                    <select name="staff[]" class="form-select" multiple>
                        @foreach($staff as $staffMember)
                            <option value="{{ $staffMember->id }}">{{ $staffMember->name }}</option>
                        @endforeach
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
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Buat Service Order</button>
            </div>
        </form>
    </div>
</div>
@endsection
