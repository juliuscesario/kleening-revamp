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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M19 16l-2 3h4l-2 3" /></svg>
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
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-danger text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-exclamation" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4c.348 0 .686 .045 1.008 .128" /><path d="M19 16v3" /><path d="M19 22v.01" /></svg>
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
                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $so->so_number }}</h5>
                                    <small>{{ $so->work_date->format('d M Y') }}</small>
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

    {{-- Recent Activity --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">Aktivitas Terkini</h3>
            </div>
            <div class="card-body card-body-scrollable card-body-scrollable-shadow">
                @if($recentActivity->isEmpty())
                    <p>Tidak ada aktivitas terkini.</p>
                @else
                    <div class="list-group">
                        @foreach($recentActivity as $so)
                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{{ $so->so_number }}</h5>
                                    <small>{{ $so->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">Status: <span class="badge bg-info text-white">{{ ucfirst($so->status) }}</span></p>
                                <small>Pelanggan: {{ $so->customer->name }}</small>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
