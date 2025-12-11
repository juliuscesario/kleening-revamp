@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tambah Alamat Baru</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('web.addresses.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select class="form-control" id="customer_id" name="customer_id" required>
                                <option value="">Pilih Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $selectedCustomerId == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="area_id" class="form-label">Area</label>
                            <select class="form-control" id="area_id" name="area_id" required>
                                <option value="">Pilih Area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="label" class="form-label">Label Alamat</label>
                            <input type="text" class="form-control" id="label" name="label" required>
                        </div>

                        <div class="mb-3">
                            <label for="contact_name" class="form-label">Nama Kontak</label>
                            <input type="text" class="form-control" id="contact_name" name="contact_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">Telepon Kontak</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" required>
                        </div>

                        <div class="mb-3">
                            <label for="full_address" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="full_address" name="full_address" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="google_maps_link" class="form-label">Link Google Maps</label>
                            <input type="url" class="form-control" id="google_maps_link" name="google_maps_link">
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
