{{-- Machine Attendance Panel --}}
@if($machineAttendanceStatus !== null)
<div id="machine-attendance-panel" class="mb-3">
    @if($machineAttendanceStatus === 'no_attendance')
        <div class="card card-sm border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-warning fw-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle me-1" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg>
                            Mesin Pergi
                        </div>
                        <small class="text-muted">Wajib upload sebelum mulai kerjaan</small>
                    </div>
                    <button class="btn btn-warning" id="btn-mesin-pergi">
                        📷 Mesin Pergi
                    </button>
                </div>
            </div>
        </div>

    @elseif($machineAttendanceStatus === 'active')
        <div class="card card-sm border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-success-lt">Mesin aktif</span>
                        <div class="mt-1">
                            <small class="text-muted">Pergi: <strong>{{ $machineAttendance->photo_pergi_at->format('H:i') }}</strong></small>
                        </div>
                        <div class="mt-1">
                            @foreach($machineAttendance->machines as $machine)
                                <span class="badge bg-secondary-lt me-1 mb-1">{{ $machine->code }}</span>
                            @endforeach
                        </div>
                        @if($machineAttendance->catatan)
                            <div class="mt-1">
                                <small class="text-muted">{{ $machineAttendance->catatan }}</small>
                            </div>
                        @endif
                    </div>
                    <button class="btn btn-primary" id="btn-mesin-pulang"
                            data-attendance-id="{{ $machineAttendance->id }}">
                        📷 Mesin Pulang
                    </button>
                </div>
            </div>
        </div>

    @elseif($machineAttendanceStatus === 'completed')
        <div class="card card-sm">
            <div class="card-body">
                <div>
                    <span class="badge bg-muted">Mesin selesai ✓</span>
                    <div class="mt-1">
                        <small class="text-muted">
                            Pergi: <strong>{{ $machineAttendance->photo_pergi_at->format('H:i') }}</strong>
                            | Pulang: <strong>{{ $machineAttendance->photo_pulang_at->format('H:i') }}</strong>
                        </small>
                    </div>
                    <div class="mt-1">
                        @foreach($machineAttendance->machines as $machine)
                            <span class="badge bg-secondary-lt me-1 mb-1">{{ $machine->code }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endif

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-outline-secondary d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sortOffcanvas" aria-controls="sortOffcanvas">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrows-sort" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
           <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
           <path d="M3 9l4 -4l4 4m-4 -4v14"></path>
           <path d="M21 15l-4 4l-4 -4m4 4v-14"></path>
        </svg>
        Urutkan Tampilan
    </button>
</div>

<div class="offcanvas offcanvas-bottom rounded-top-3" tabindex="-1" id="sortOffcanvas" aria-labelledby="sortOffcanvasLabel" style="height: auto; min-height: 40vh;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="sortOffcanvasLabel">Pengaturan Tampilan</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form action="{{ url()->current() }}" method="GET">
            <div class="mb-4">
                <label class="form-label fw-bold mb-3">Urutkan Berdasarkan</label>
                <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column gap-2">
                    <label class="form-selectgroup-item flex-fill">
                        <input type="radio" name="sort_by" value="tanggal" class="form-selectgroup-input" {{ ($currentSortBy ?? 'tanggal') == 'tanggal' ? 'checked' : '' }}>
                        <span class="form-selectgroup-label d-flex align-items-center p-3">
                            <span class="me-3">
                                <span class="form-selectgroup-check"></span>
                            </span>
                            <span class="form-selectgroup-label-content">
                                <span class="d-block fw-bold">Tanggal Sesi</span>
                                <span class="d-block text-muted mt-1 small">Urutkan berdasarkan tanggal & waktu sesi</span>
                            </span>
                        </span>
                    </label>
                    <label class="form-selectgroup-item flex-fill">
                        <input type="radio" name="sort_by" value="jam" class="form-selectgroup-input" {{ ($currentSortBy ?? 'tanggal') == 'jam' ? 'checked' : '' }}>
                        <span class="form-selectgroup-label d-flex align-items-center p-3">
                            <span class="me-3">
                                <span class="form-selectgroup-check"></span>
                            </span>
                            <span class="form-selectgroup-label-content">
                                <span class="d-block fw-bold">Waktu Sesi</span>
                                <span class="d-block text-muted mt-1 small">Urutkan berdasarkan jam sesi</span>
                            </span>
                        </span>
                    </label>
                    <label class="form-selectgroup-item flex-fill">
                        <input type="radio" name="sort_by" value="created_at" class="form-selectgroup-input" {{ ($currentSortBy ?? 'tanggal') == 'created_at' ? 'checked' : '' }}>
                        <span class="form-selectgroup-label d-flex align-items-center p-3">
                            <span class="me-3">
                                <span class="form-selectgroup-check"></span>
                            </span>
                            <span class="form-selectgroup-label-content">
                                <span class="d-block fw-bold">Waktu Dibuat</span>
                                <span class="d-block text-muted mt-1 small">Urutkan berdasarkan kapan sesi dibuat</span>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold mb-3">Urutan</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="sort_dir" id="sortAsc" value="asc" {{ ($currentSortDir ?? 'asc') == 'asc' ? 'checked' : '' }} autocomplete="off">
                    <label class="btn btn-outline-primary w-50 py-2" for="sortAsc">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-sort-ascending" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                           <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                           <path d="M4 6l7 0"></path>
                           <path d="M4 12l7 0"></path>
                           <path d="M4 18l9 0"></path>
                           <path d="M15 9l3 -3l3 3"></path>
                           <path d="M18 6l0 12"></path>
                        </svg>
                        Terlama - Terbaru
                    </label>

                    <input type="radio" class="btn-check" name="sort_dir" id="sortDesc" value="desc" {{ ($currentSortDir ?? 'asc') == 'desc' ? 'checked' : '' }} autocomplete="off">
                    <label class="btn btn-outline-primary w-50 py-2" for="sortDesc">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-sort-descending" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                           <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                           <path d="M4 6l9 0"></path>
                           <path d="M4 12l7 0"></path>
                           <path d="M4 18l7 0"></path>
                           <path d="M15 15l3 3l3 -3"></path>
                           <path d="M18 6l0 12"></path>
                        </svg>
                        Terbaru - Terlama
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fs-3">
                Terapkan Pengaturan
            </button>
        </form>
    </div>
</div>

<div class="row mb-3 g-2">
    @if($doneSessions->isNotEmpty())
        <div class="col">
            <button type="button" class="btn btn-outline-success w-100 text-wrap h-auto py-2" data-bs-toggle="modal" data-bs-target="#doneOrdersModal">
                <span class="d-none d-sm-inline">Lihat Sesi </span>Selesai ({{ $doneSessions->count() }})
            </button>
        </div>
    @endif
    @if($cancelledSessions->isNotEmpty())
        <div class="col">
            <button type="button" class="btn btn-outline-secondary w-100 text-wrap h-auto py-2" data-bs-toggle="modal" data-bs-target="#cancelledOrdersModal">
                <span class="d-none d-sm-inline">Lihat Sesi </span>Dibatalkan ({{ $cancelledSessions->count() }})
            </button>
        </div>
    @endif
</div>

<div class="row row-deck row-cards">
    {{-- Staff Stats Widgets --}}
    <div class="col-12">
        <div class="row row-cards flex-nowrap overflow-auto pb-2" style="scrollbar-width: none; -ms-overflow-style: none;">
            <div class="col-10 col-sm-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-success text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 12l5 5l10 -10" /><path d="M2 12l5 5m5 -5l5 -5" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $totalDoneCount }} Total Selesai
                                </div>
                                <div class="text-muted">
                                    Semua waktu
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-10 col-sm-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M19 16l-2 3h4l-2 3" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $todayDoneCount }} Selesai Hari Ini
                                </div>
                                <div class="text-muted">
                                    {{ \Carbon\Carbon::today()->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-10 col-sm-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-warning text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 21h-9a3 3 0 0 1 -3 -3v-1h10v2a2 2 0 0 0 4 0v-2h-10v-6h4" /><path d="M11 7.5m-1 .5a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M6.5 11.5m-1 .5a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M6 15h2" /><path d="M5 18h2" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $bookedCount }} Dibooking
                                </div>
                                <div class="text-muted">
                                    Jadwal mendatang
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Sessions --}}
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Jadwal Hari Ini</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($todaySessions->isEmpty())
                    <p>Tidak ada jadwal untuk hari ini.</p>
                @else
                    <div class="list-group">
                        @foreach($todaySessions as $session)
                            <a href="{{ route('web.service-orders.show', $session->serviceOrder->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $session->serviceOrder->so_number }}</h5>
                                            <span class="badge bg-{{ $session->status == 'proses' ? 'info' : ($session->status == 'done' ? 'success' : 'primary') }} text-white">{{ ucfirst($session->status) }}</span>
                                            @if($session->session_number > 1)
                                                <span class="badge bg-teal text-white">Sesi {{ $session->session_number }}</span>
                                            @endif
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $session->serviceOrder->customer->name ?? '-' }}</p>
                                        @php
                                            $firstItem = $session->serviceOrder->items->first();
                                            $categoryName = $firstItem?->service?->category?->name ?? null;
                                        @endphp
                                        @if($categoryName)
                                            <p class="mb-0 text-muted small">{{ $categoryName }}</p>
                                        @endif
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</div>
                                        @if($session->jam)
                                            <div class="text-primary fw-semibold">{{ $session->jam }} WIB</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tomorrow's Sessions --}}
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Jadwal Besok</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($tomorrowSessions->isEmpty())
                    <p>Tidak ada jadwal untuk besok.</p>
                @else
                    <div class="list-group">
                        @foreach($tomorrowSessions as $session)
                            <a href="{{ route('web.service-orders.show', $session->serviceOrder->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $session->serviceOrder->so_number }}</h5>
                                            <span class="badge bg-primary text-white">{{ ucfirst($session->status) }}</span>
                                            @if($session->session_number > 1)
                                                <span class="badge bg-teal text-white">Sesi {{ $session->session_number }}</span>
                                            @endif
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $session->serviceOrder->customer->name ?? '-' }}</p>
                                        @php
                                            $firstItem = $session->serviceOrder->items->first();
                                            $categoryName = $firstItem?->service?->category?->name ?? null;
                                        @endphp
                                        @if($categoryName)
                                            <p class="mb-0 text-muted small">{{ $categoryName }}</p>
                                        @endif
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</div>
                                        @if($session->jam)
                                            <div class="text-primary fw-semibold">{{ $session->jam }} WIB</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Past Sessions --}}
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Jadwal Terlewat</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($pastSessions->isEmpty())
                    <p>Tidak ada jadwal dari tanggal lalu.</p>
                @else
                    <div class="list-group">
                        @foreach($pastSessions as $session)
                            <a href="{{ route('web.service-orders.show', $session->serviceOrder->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $session->serviceOrder->so_number }}</h5>
                                            <span class="badge bg-{{ $session->status == 'proses' ? 'info' : ($session->status == 'done' ? 'success' : 'primary') }} text-white">{{ ucfirst($session->status) }}</span>
                                            @if($session->session_number > 1)
                                                <span class="badge bg-teal text-white">Sesi {{ $session->session_number }}</span>
                                            @endif
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $session->serviceOrder->customer->name ?? '-' }}</p>
                                        @php
                                            $firstItem = $session->serviceOrder->items->first();
                                            $categoryName = $firstItem?->service?->category?->name ?? null;
                                        @endphp
                                        @if($categoryName)
                                            <p class="mb-0 text-muted small">{{ $categoryName }}</p>
                                        @endif
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</div>
                                        @if($session->jam)
                                            <div class="text-primary fw-semibold">{{ $session->jam }} WIB</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modals for Done and Cancelled sessions --}}
<div class="modal modal-blur fade" id="doneOrdersModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sesi Selesai (10 Hari Terakhir)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($doneSessions->isEmpty())
                    <p>Tidak ada sesi yang selesai.</p>
                @else
                    <div class="list-group">
                        @foreach($doneSessions as $session)
                            <a href="{{ route('web.service-orders.show', $session->serviceOrder->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $session->serviceOrder->so_number }}</h5>
                                            <span class="badge bg-success text-white">{{ ucfirst($session->status) }}</span>
                                            @if($session->session_number > 1)
                                                <span class="badge bg-teal text-white">Sesi {{ $session->session_number }}</span>
                                            @endif
                                        </div>
                                        <p class="mb-0 text-secondary">
                                            <strong>Pelanggan:</strong> {{ $session->serviceOrder->customer->name ?? '-' }}<br>
                                            <strong>No. HP:</strong> {{ $session->serviceOrder->customer->phone_number ?? '-' }}<br>
                                            <strong>Alamat:</strong> {{ $session->serviceOrder->address->full_address ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</div>
                                        @if($session->jam)
                                            <div class="text-primary fw-semibold">{{ $session->jam }} WIB</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="cancelledOrdersModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sesi Dibatalkan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($cancelledSessions->isEmpty())
                    <p>Tidak ada sesi yang dibatalkan.</p>
                @else
                    <div class="list-group">
                        @foreach($cancelledSessions as $session)
                            <a href="{{ route('web.service-orders.show', $session->serviceOrder->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $session->serviceOrder->so_number }}</h5>
                                            <span class="badge bg-secondary text-white">{{ ucfirst($session->status) }}</span>
                                            @if($session->session_number > 1)
                                                <span class="badge bg-teal text-white">Sesi {{ $session->session_number }}</span>
                                            @endif
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $session->serviceOrder->customer->name ?? '-' }}</p>
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5">{{ \Carbon\Carbon::parse($session->tanggal)->format('d M Y') }}</div>
                                        @if($session->jam)
                                            <div class="text-primary fw-semibold">{{ $session->jam }} WIB</div>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Mesin Pergi Modal --}}
<div class="modal modal-blur" id="modal-mesin-pergi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mesin Pergi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Foto Mesin</label>
                    <input type="file" accept="image/*" capture="camera"
                           id="pergi-photo-input" class="form-control">
                    <div id="pergi-photo-preview" class="mt-2" style="display:none;">
                        <img id="pergi-preview-img" class="rounded" style="max-width:100%; max-height:200px;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Pilih Mesin</label>
                    <div id="machine-checklist">
                        <div class="text-muted">Memuat daftar mesin...</div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan</label>
                    <textarea id="pergi-catatan" class="form-control" rows="2"
                              placeholder="Contoh: hv3 selang bocor"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="btn-submit-pergi">
                    Simpan Mesin Pergi
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Mesin Pulang Modal --}}
<div class="modal modal-blur" id="modal-mesin-pulang" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mesin Pulang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Mesin yang dikembalikan</label>
                    <div id="pulang-machine-list"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Foto Mesin</label>
                    <input type="file" accept="image/*" capture="camera"
                           id="pulang-photo-input" class="form-control">
                    <div id="pulang-photo-preview" class="mt-2" style="display:none;">
                        <img id="pulang-preview-img" class="rounded" style="max-width:100%; max-height:200px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-submit-pulang">
                    Simpan Mesin Pulang
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    if (!document.getElementById('machine-attendance-panel')) return;

    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function compressImage(file, maxWidth = 800, quality = 0.6) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }
                    canvas.width = width;
                    canvas.height = height;
                    canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                    canvas.toBlob(resolve, 'image/jpeg', quality);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    document.getElementById('pergi-photo-input')?.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('pergi-preview-img').src = e.target.result;
                document.getElementById('pergi-photo-preview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('pulang-photo-input')?.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('pulang-preview-img').src = e.target.result;
                document.getElementById('pulang-photo-preview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('btn-mesin-pergi')?.addEventListener('click', function() {
        document.getElementById('pergi-photo-input').value = '';
        document.getElementById('pergi-photo-preview').style.display = 'none';
        document.getElementById('pergi-catatan').value = '';

        fetch('/machine-attendance/available-machines', {
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(grouped => renderMachineChecklist(grouped))
        .catch(() => {
            document.getElementById('machine-checklist').innerHTML =
                '<div class="text-danger">Gagal memuat daftar mesin</div>';
        });

        new bootstrap.Modal(document.getElementById('modal-mesin-pergi')).show();
    });

    function renderMachineChecklist(grouped) {
        let html = '';
        for (const [category, machines] of Object.entries(grouped)) {
            // Derive a safe slug from the category name for JS logic
            const slug = category.toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9\-]/g, '');
            html += `<div class="mb-2"><strong class="text-muted small">${category}</strong><div class="ms-2">`;
            machines.forEach(m => {
                const disabled = !m.available ? 'disabled' : '';
                const labelClass = !m.available ? 'text-muted' : '';
                const suffix = !m.available ? ` (dibawa ${m.used_by})` : '';
                const paired = m.paired_machine_id ? `data-paired="${m.paired_machine_id}"` : '';
                html += `<label class="form-check">
                    <input type="checkbox" class="form-check-input machine-checkbox"
                           value="${m.id}" data-code="${m.code}" data-category-id="${m.category_id}"
                           data-category-slug="${slug}"
                           ${paired} ${disabled}>
                    <span class="form-check-label ${labelClass}">${m.code}${suffix}</span>
                </label>`;
            });
            html += '</div></div>';
        }
        document.getElementById('machine-checklist').innerHTML = html;
        applyAutoSteamRules();
    }

    function applyAutoSteamRules() {
        document.querySelectorAll('.machine-checkbox').forEach(cb => {
            const pairedId = cb.dataset.paired;
            if (pairedId) {
                const paired = document.querySelector(`.machine-checkbox[value="${pairedId}"]`);
                if (paired && paired.disabled && !cb.disabled) {
                    const label = cb.closest('label').querySelector('.form-check-label');
                    const pairedCode = paired.dataset.code;
                    const usedByMatch = paired.closest('label').querySelector('.form-check-label').textContent.match(/dibawa (.+)\)/);
                    const name = usedByMatch ? usedByMatch[1] : 'staff lain';
                    label.textContent = `${cb.dataset.code} (steam ${pairedCode} dibawa ${name})`;
                }
            }
        });
    }

    // ── Bug 1: Enforce max-1-per-category and HV/PW mutual exclusivity ──
    function getCategorySlug(cb) {
        return cb.dataset.categorySlug || '';
    }

    function enforceCategoryRules(checkedCb) {
        const checkedCategorySlug = getCategorySlug(checkedCb);
        if (!checkedCategorySlug) return;

        const allCheckboxes = document.querySelectorAll('.machine-checkbox');

        // HV ↔ PW mutual exclusivity
        if (checkedCategorySlug === 'hydrovacuum') {
            allCheckboxes.forEach(cb => {
                const slug = getCategorySlug(cb);
                if (slug === 'premium-wash') {
                    cb.checked = false;
                    cb.disabled = true;
                }
            });
        } else if (checkedCategorySlug === 'premium-wash') {
            allCheckboxes.forEach(cb => {
                const slug = getCategorySlug(cb);
                if (slug === 'hydrovacuum') {
                    cb.checked = false;
                    cb.disabled = true;
                }
            });
        }

        // Re-enable both groups if no HV and no PW are checked
        const anyHVChecked = [...allCheckboxes].some(cb => getCategorySlug(cb) === 'hydrovacuum' && cb.checked);
        const anyPWChecked = [...allCheckboxes].some(cb => getCategorySlug(cb) === 'premium-wash' && cb.checked);
        if (!anyHVChecked && !anyPWChecked) {
            allCheckboxes.forEach(cb => {
                const slug = getCategorySlug(cb);
                if (slug === 'hydrovacuum' || slug === 'premium-wash') {
                    cb.disabled = false;
                }
            });
        }

        // Max 1 per category (HV, Steam, PW, GC): uncheck all others in same category
        const checkedCategoryId = checkedCb.dataset.categoryId;
        allCheckboxes.forEach(cb => {
            if (cb === checkedCb) return;
            if (cb.dataset.categoryId === checkedCategoryId) {
                cb.checked = false;
            }
        });
    }

    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('machine-checkbox')) return;

        // Bug 2 fix: Only handle HV→Steam direction (not reverse)
        const pairedId = e.target.dataset.paired;
        if (pairedId) {
            const paired = document.querySelector(`.machine-checkbox[value="${pairedId}"]`);
            if (paired) {
                const pairedCategorySlug = getCategorySlug(paired);
                const thisCategorySlug = getCategorySlug(e.target);

                // HV→Steam: when HV is checked/unchecked, mirror to paired steam
                if (thisCategorySlug === 'hydrovacuum' && pairedCategorySlug === 'steam') {
                    paired.checked = e.target.checked;
                }
                // Steam→HV: do NOT auto-check HV when steam is checked (bug 2 fix)
            }
        }

        // Bug 1: enforce max-1-per-category and HV/PW mutual exclusivity
        if (e.target.checked) {
            enforceCategoryRules(e.target);
        } else {
            // Re-enable HV/PW groups when all are unchecked
            reEnableMutuallyExclusiveGroups();
        }
    });

    function reEnableMutuallyExclusiveGroups() {
        const allCheckboxes = document.querySelectorAll('.machine-checkbox');
        const anyHVChecked = [...allCheckboxes].some(cb => getCategorySlug(cb) === 'hydrovacuum' && cb.checked);
        const anyPWChecked = [...allCheckboxes].some(cb => getCategorySlug(cb) === 'premium-wash' && cb.checked);
        if (!anyHVChecked && !anyPWChecked) {
            allCheckboxes.forEach(cb => {
                const slug = getCategorySlug(cb);
                if (slug === 'hydrovacuum' || slug === 'premium-wash') {
                    cb.disabled = false;
                }
            });
        }
    }

    document.getElementById('btn-submit-pergi')?.addEventListener('click', async function() {
        const file = document.getElementById('pergi-photo-input').files[0];
        if (!file) { Swal.fire('Error', 'Foto mesin wajib diupload', 'error'); return; }

        const machineIds = [];
        document.querySelectorAll('.machine-checkbox:checked').forEach(cb => machineIds.push(cb.value));
        if (machineIds.length === 0) { Swal.fire('Error', 'Pilih minimal 1 mesin', 'error'); return; }

        this.disabled = true;
        this.textContent = 'Menyimpan...';

        try {
            const compressed = await compressImage(file);
            const formData = new FormData();
            formData.append('photo', compressed, 'mesin_pergi.jpg');
            machineIds.forEach(id => formData.append('machine_ids[]', id));
            const catatan = document.getElementById('pergi-catatan').value;
            if (catatan) formData.append('catatan', catatan);

            const res = await fetch('/machine-attendance/pergi', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modal-mesin-pergi')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
                this.disabled = false;
                this.textContent = 'Simpan Mesin Pergi';
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal menyimpan', 'error');
            this.disabled = false;
            this.textContent = 'Simpan Mesin Pergi';
        }
    });

    document.getElementById('btn-mesin-pulang')?.addEventListener('click', function() {
        document.getElementById('pulang-photo-input').value = '';
        document.getElementById('pulang-photo-preview').style.display = 'none';

        const machines = [];
        document.querySelectorAll('#machine-attendance-panel .badge.bg-secondary-lt').forEach(badge => {
            machines.push(badge.textContent.trim());
        });
        document.getElementById('pulang-machine-list').innerHTML =
            machines.map(code => `<span class="badge bg-secondary-lt me-1 mb-1">${code}</span>`).join('');

        new bootstrap.Modal(document.getElementById('modal-mesin-pulang')).show();
    });

    document.getElementById('btn-submit-pulang')?.addEventListener('click', async function() {
        const file = document.getElementById('pulang-photo-input').files[0];
        if (!file) { Swal.fire('Error', 'Foto mesin wajib diupload', 'error'); return; }

        const attendanceId = document.getElementById('btn-mesin-pulang')?.dataset.attendanceId;
        if (!attendanceId) { Swal.fire('Error', 'Data attendance tidak ditemukan', 'error'); return; }

        this.disabled = true;
        this.textContent = 'Menyimpan...';

        try {
            const compressed = await compressImage(file);
            const formData = new FormData();
            formData.append('photo', compressed, 'mesin_pulang.jpg');

            const res = await fetch(`/machine-attendance/${attendanceId}/pulang`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modal-mesin-pulang')).hide();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
                this.disabled = false;
                this.textContent = 'Simpan Mesin Pulang';
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal menyimpan', 'error');
            this.disabled = false;
            this.textContent = 'Simpan Mesin Pulang';
        }
    });
})();
</script>
@endpush
