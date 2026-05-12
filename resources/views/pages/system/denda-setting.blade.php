@extends('layouts.admin')

@section('title', 'Denda Setting')

@section('content')
<div class="container-xl">
    <!-- Page-header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Denda Setting</h2>
                <div class="text-muted mt-1">Konfigurasi nilai denda untuk payroll</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('system.denda-setting.update') }}" method="POST">
                    @csrf

                    {{-- KETERLAMBATAN --}}
                    <h5 class="mb-3">Keterlambatan</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Batas Telat (menit)</label>
                            <input type="number" name="denda_telat_threshold" class="form-control"
                                   value="{{ old('denda_telat_threshold', $settings['denda_telat_threshold']) }}"
                                   min="0" step="1" required>
                            <div class="form-text">Denda berlaku jika telat lebih dari X menit</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Denda Telat (ribu)</label>
                            <input type="number" name="denda_telat_amount" class="form-control"
                                   value="{{ old('denda_telat_amount', $settings['denda_telat_amount']) }}"
                                   min="0" step="1" required>
                        </div>
                    </div>

                    <hr>

                    {{-- FOTO DOKUMENTASI --}}
                    <h5 class="mb-3 mt-4">Foto Dokumentasi</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Denda Before (ribu)</label>
                            <input type="number" name="denda_before_photo_amount" class="form-control"
                                   value="{{ old('denda_before_photo_amount', $settings['denda_before_photo_amount']) }}"
                                   min="0" step="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Denda After (ribu)</label>
                            <input type="number" name="denda_after_photo_amount" class="form-control"
                                   value="{{ old('denda_after_photo_amount', $settings['denda_after_photo_amount']) }}"
                                   min="0" step="1" required>
                        </div>
                    </div>
                    <div class="form-text mb-4">Denda jika foto before/after tidak diupload</div>

                    <hr>

                    {{-- MESIN ABSENSI --}}
                    <h5 class="mb-3 mt-4">Mesin Absensi</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Denda M. Pergi (ribu)</label>
                            <input type="number" name="denda_mesin_pergi_amount" class="form-control"
                                   value="{{ old('denda_mesin_pergi_amount', $settings['denda_mesin_pergi_amount']) }}"
                                   min="0" step="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Denda M. Pulang (ribu)</label>
                            <input type="number" name="denda_mesin_pulang_amount" class="form-control"
                                   value="{{ old('denda_mesin_pulang_amount', $settings['denda_mesin_pulang_amount']) }}"
                                   min="0" step="1" required>
                        </div>
                    </div>
                    <div class="form-text mb-4">Denda jika foto mesin absen tidak diupload</div>

                    {{-- INFO NOTE --}}
                    <div class="alert alert-info">
                        <strong>ℹ</strong> Semua nilai denda dalam satuan ribu (rb). Denda akan diterapkan sebagai nilai negatif (-) di payroll Excel.
                    </div>

                    {{-- SUBMIT --}}
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            💾 Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
