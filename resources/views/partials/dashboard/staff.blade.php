<div class="row mb-3 g-2">
    @if($doneServiceOrders->isNotEmpty())
        <div class="col-md">
            <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#doneOrdersModal">
                Lihat Service Orders Selesai ({{ $doneServiceOrders->count() }})
            </button>
        </div>
    @endif
    @if($cancelledServiceOrders->isNotEmpty())
        <div class="col-md">
            <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#cancelledOrdersModal">
                Lihat Service Orders Dibatalkan ({{ $cancelledServiceOrders->count() }})
            </button>
        </div>
    @endif
</div>

<div class="row row-deck row-cards">
    {{-- Staff Stats Widgets --}}
    <div class="col-12">
        <div class="row row-cards">
            <div class="col-sm-4">
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
            <div class="col-sm-4">
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
            <div class="col-sm-4">
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

    {{-- Today's Service Orders --}}
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Service Orders Hari Ini</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($todayServiceOrders->isEmpty())
                    <p>Tidak ada Service Order untuk hari ini.</p>
                @else
                    <div class="list-group">
                        @foreach($todayServiceOrders as $so)
                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $so->so_number }}</h5>
                                            <span class="badge bg-{{ $so->status == 'proses' ? 'info' : 'primary' }} text-white">{{ ucfirst($so->status) }}</span>
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $so->customer->name }}</p>
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5 text-dark">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</div>
                                        @if($so->work_time_formatted)
                                            <div class="text-primary fw-semibold">{{ $so->work_time_formatted }} WIB</div>
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

    {{-- Tomorrow's Service Orders --}}
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Service Orders Besok</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($tomorrowServiceOrders->isEmpty())
                    <p>Tidak ada Service Order untuk besok.</p>
                @else
                    <div class="list-group">
                        @foreach($tomorrowServiceOrders as $so)
                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $so->so_number }}</h5>
                                            <span class="badge bg-{{ $so->status == 'proses' ? 'info' : 'primary' }} text-white">{{ ucfirst($so->status) }}</span>
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $so->customer->name }}</p>
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5 text-dark">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</div>
                                        @if($so->work_time_formatted)
                                            <div class="text-primary fw-semibold">{{ $so->work_time_formatted }} WIB</div>
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

    {{-- Past Service Orders --}}
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Service Orders Terlewat</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($pastServiceOrders->isEmpty())
                    <p>Tidak ada Service Order dari tanggal lalu.</p>
                @else
                    <div class="list-group">
                        @foreach($pastServiceOrders as $so)
                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $so->so_number }}</h5>
                                            <span class="badge bg-{{ $so->status == 'proses' ? 'info' : 'primary' }} text-white">{{ ucfirst($so->status) }}</span>
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $so->customer->name }}</p>
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5 text-dark">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</div>
                                        @if($so->work_time_formatted)
                                            <div class="text-primary fw-semibold">{{ $so->work_time_formatted }} WIB</div>
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

{{-- Modals for Done and Cancelled orders --}}
<div class="modal modal-blur fade" id="doneOrdersModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Service Orders Selesai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($doneServiceOrders->isEmpty())
                    <p>Tidak ada Service Order yang selesai.</p>
                @else
                    <div class="list-group">
                        @foreach($doneServiceOrders as $so)
                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $so->so_number }}</h5>
                                            <span class="badge bg-success text-white">Done</span>
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $so->customer->name }}</p>
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5 text-dark">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</div>
                                        @if($so->work_time_formatted)
                                            <div class="text-primary fw-semibold">{{ $so->work_time_formatted }} WIB</div>
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
                <h5 class="modal-title">Service Orders Dibatalkan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($cancelledServiceOrders->isEmpty())
                    <p>Tidak ada Service Order yang dibatalkan.</p>
                @else
                    <div class="list-group">
                        @foreach($cancelledServiceOrders as $so)
                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex flex-column flex-sm-row w-100 align-items-start gap-2">
                                    <div class="flex-grow-1 order-2 order-sm-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h5 class="mb-0">{{ $so->so_number }}</h5>
                                            <span class="badge bg-secondary text-white">Cancelled</span>
                                        </div>
                                        <p class="mb-0 text-secondary">Pelanggan: {{ $so->customer->name }}</p>
                                    </div>
                                    <div class="order-1 order-sm-2 text-sm-end">
                                        <div class="fw-bold fs-5 text-dark">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</div>
                                        @if($so->work_time_formatted)
                                            <div class="text-primary fw-semibold">{{ $so->work_time_formatted }} WIB</div>
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
