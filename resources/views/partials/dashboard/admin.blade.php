<div class="row row-deck row-cards">
    {{-- Admin Widgets --}}
    <div class="col-12">
        <div class="row row-cards">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                        <path d="M19 16l-2 3h4l-2 3" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $todaySchedule->count() }} Pekerjaan
                                </div>
                                <div class="text-muted">
                                    Jadwal Hari Ini
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="{{ route('web.service-orders.unassigned') }}" class="card card-sm card-link">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-danger text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-user-exclamation" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4c.348 0 .686 .045 1.008 .128" />
                                        <path d="M19 16v3" />
                                        <path d="M19 22v.01" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $unassignedJobs->count() }} Pekerjaan
                                </div>
                                <div class="text-muted">
                                    Belum Ada Staff
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-warning text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-file-dollar" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                        <path
                                            d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                        <path d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5" />
                                        <path d="M12 17v1m0 -8v1" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    {{ $doneNotInvoiced->count() }} Pekerjaan
                                </div>
                                <div class="text-muted">
                                    Done Belum Invoice
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <a href="{{ route('web.service-orders.create') }}" class="btn btn-primary w-100">
                            Buat Service Order
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Schedule --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Jadwal Hari Ini</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($todaySchedule->isEmpty())
                    <p>Tidak ada jadwal untuk hari ini.</p>
                @else
                    <div class="list-group">
                        @foreach($todaySchedule as $so)
                            <a href="{{ route('web.service-orders.show', $so->id) }}"
                                class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <h5 class="mb-1">{{ $so->so_number }}</h5>
                                    <div class="text-end">
                                        <div class="fw-bold text-dark">{{ $so->work_date->format('d M Y') }}</div>
                                        @if($so->work_time_formatted)
                                            <div class="text-primary fw-semibold">{{ $so->work_time_formatted }} WIB</div>
                                        @endif
                                    </div>
                                </div>
                                <p class="mb-1">Pelanggan: {{ $so->customer->name }}</p>
                                <small>Staff: {{ $so->staff->pluck('name')->join(', ') ?: 'Belum ada' }}</small>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tomorrow's Schedule --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Jadwal Besok</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($tomorrowSchedule->isEmpty())
                    <p>Tidak ada jadwal untuk besok.</p>
                @else
                    <div class="list-group">
                        @foreach($tomorrowSchedule as $so)
                            <a href="{{ route('web.service-orders.show', $so->id) }}"
                                class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <h5 class="mb-1">{{ $so->so_number }}</h5>
                                    <div class="text-end">
                                        <div class="fw-bold text-dark">{{ $so->work_date->format('d M Y') }}</div>
                                        @if($so->work_time_formatted)
                                            <div class="text-primary fw-semibold">{{ $so->work_time_formatted }} WIB</div>
                                        @endif
                                    </div>
                                </div>
                                <p class="mb-1">Pelanggan: {{ $so->customer->name }}</p>
                                <small>Staff: {{ $so->staff->pluck('name')->join(', ') ?: 'Belum ada' }}</small>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Unassigned Jobs --}}
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h3 class="card-title">Pekerjaan Belum Ada Staff</h3>
                <a href="{{ route('web.service-orders.unassigned') }}" class="btn btn-sm btn-ghost-primary">Lihat
                    Semua</a>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow" style="max-height: 400px;">
                @if($unassignedJobs->isEmpty())
                    <p>Tidak ada pekerjaan yang belum memiliki staff.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-mobile-md card-table">
                            <thead>
                                <tr>
                                    <th>No. SO</th>
                                    <th>Pelanggan</th>
                                    <th>Jadwal</th>
                                    <th class="w-1">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unassignedJobs as $job)
                                    <tr>
                                        <td data-label="No. SO">
                                            <div class="font-weight-medium">{{ $job->so_number }}</div>
                                        </td>
                                        <td data-label="Pelanggan">
                                            <div>{{ $job->customer->name }}</div>
                                            <div class="text-muted small">{{ $job->address->full_address ?? '-' }}</div>
                                        </td>
                                        <td data-label="Jadwal">
                                            <div>{{ $job->work_date->format('d M Y') }}</div>
                                            <div class="text-muted small">
                                                {{ $job->work_time_formatted ? $job->work_time_formatted . ' WIB' : '-' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="{{ route('web.service-orders.show', $job->id) }}"
                                                    class="btn btn-white btn-sm">
                                                    Assign Staff
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>