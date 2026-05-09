@extends('layouts.admin')
@section('title', 'Laporan Absensi Mesin')

@section('content')
<div class="container-xl" id="machine-attendance-report-page">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Absensi Mesin</h2>
                <div class="text-muted mt-1">Rekap absensi mesin staff dengan foto dan durasi.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        {{-- Filter Section --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-2 col-6">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" id="filter-date-from" class="form-control">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" id="filter-date-to" class="form-control">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">Area</label>
                        <select id="filter-area" class="form-select">
                            <option value="">Semua Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">Staff</label>
                        <select id="filter-staff" class="form-select">
                            <option value="">Semua Staff</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">Kategori Mesin</label>
                        <select id="filter-category" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label">Status</label>
                        <select id="filter-status" class="form-select">
                            <option value="">Semua</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="force_closed">Force Closed</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Total Records</div>
                        </div>
                        <div class="h1 mb-0" id="stat-total">—</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">Open (Belum Pulang)</div>
                        </div>
                        <div class="h1 mb-0 text-danger" id="stat-open">—</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="subheader">With Warnings</div>
                        </div>
                        <div class="h1 mb-0 text-warning" id="stat-warnings">—</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Absensi Mesin</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="machine-attendance-report-table" class="table table-vcenter table-hover datatable"
                           data-url="{{ route('data.reports.machine-attendance') }}">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Staff</th>
                                <th>Area</th>
                                <th>Mesin</th>
                                <th>Kategori</th>
                                <th>Jam Pergi</th>
                                <th>Jam Pulang</th>
                                <th>Durasi</th>
                                <th>Catatan</th>
                                <th>Status</th>
                                <th>Warning</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Photo Viewer Modal --}}
<div class="modal modal-blur" id="modal-photo-view" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body text-center p-0">
                <img id="photo-full-view" src="" class="img-fluid rounded" style="max-height:80vh; width:auto;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
