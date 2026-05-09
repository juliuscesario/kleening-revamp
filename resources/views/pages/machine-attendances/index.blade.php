@extends('layouts.admin')
@section('title', 'Manajemen Absensi Mesin')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Manajemen Absensi Mesin</h2>
                <div class="text-muted mt-1">Kelola absensi mesin staff — lihat, edit catatan, force close, atau hapus.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-body">
                {{-- Filter Bar --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" class="form-control" id="filter-date-from">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="filter-date-to">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Staff</label>
                        <select class="form-select" id="filter-staff">
                            <option value="">Semua Staff</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Area</label>
                        <select class="form-select" id="filter-area">
                            <option value="">Semua Area</option>
                            @foreach($areas as $a)
                                <option value="{{ $a->id }}">{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="filter-status">
                            <option value="">Semua Status</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="machine-attendances-table" class="table card-table table-vcenter text-nowrap datatable"
                        data-url="{{ route('data.machine-attendances') }}"
                        data-api-url="{{ url('api/machine-attendances') }}">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Staff</th>
                                <th>Mesin</th>
                                <th>Jam Pergi</th>
                                <th>Jam Pulang</th>
                                <th>Catatan</th>
                                <th>Status</th>
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

{{-- View Modal --}}
<div class="modal modal-blur fade" id="modal-view-attendance" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Absensi Mesin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <span class="text-muted">Staff:</span>
                        <div id="view-staff-name" class="fw-bold"></div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Tanggal:</span>
                        <div id="view-date" class="fw-bold"></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <span class="text-muted">Mesin:</span>
                        <div id="view-machines" class="fw-bold"></div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Status:</span>
                        <div id="view-status"></div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <span class="text-muted d-block mb-1">Foto Pergi:</span>
                        <div id="view-photo-pergi" class="text-center"></div>
                        <div class="text-center mt-1">
                            <span class="text-muted">Jam:</span> <span id="view-photo-pergi-at"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block mb-1">Foto Pulang:</span>
                        <div id="view-photo-pulang" class="text-center"></div>
                        <div class="text-center mt-1">
                            <span class="text-muted">Jam:</span> <span id="view-photo-pulang-at"></span>
                        </div>
                    </div>
                </div>
                <hr>
                <div>
                    <span class="text-muted">Catatan:</span>
                    <div id="view-catatan" class="mt-1"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger forceCloseAttendance d-none" id="btn-force-close" data-id="">Force Close</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Edit Catatan Modal --}}
<div class="modal modal-blur fade" id="modal-edit-catatan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-attendance-id">
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea class="form-control" id="edit-catatan" rows="3" placeholder="Tambahkan catatan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnsave-catatan">Simpan</button>
            </div>
        </div>
    </div>
</div>

{{-- Photo Full View Modal --}}
<div class="modal modal-blur fade" id="modal-photo-full" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="photo-full-image" src="" alt="Foto" style="max-width: 100%; max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>
@endsection

{{-- machine-attendances.js is loaded via dynamic import in app.js when #machine-attendances-table exists --}}
