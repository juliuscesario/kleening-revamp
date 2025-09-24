@extends('layouts.admin')

@section('title', 'Dashboard Utama')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            {{-- Button to open Cancelled Orders Modal --}}
            @if($cancelledServiceOrders->isNotEmpty())
                <div class="text-end mb-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cancelledOrdersModal">
                        Lihat Service Orders Dibatalkan ({{ $cancelledServiceOrders->count() }})
                    </button>
                </div>
            @endif

            <div class="row row-deck row-cards">
                @if(Auth::user()->role === 'staff')

                    {{-- Today's Service Orders --}}
                    <div class="col-lg-4 col-md-12 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title">Service Orders Hari Ini</h3>
                            </div>
                            <div class="card-body">
                                @if($todayServiceOrders->isEmpty())
                                    <p>Tidak ada Service Order untuk hari ini.</p>
                                @else
                                    <div class="list-group">
                                        @foreach($todayServiceOrders as $so)
                                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action flex-column align-items-start">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1">{{ $so->so_number }}</h5>
                                                    <div>
                                                        <span class="badge bg-{{ $so->status == 'proses' ? 'info' : 'primary' }} text-white me-1">{{ ucfirst($so->status) }}</span>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</small>
                                                    </div>
                                                </div>
                                                <p class="mb-1">Pelanggan: {{ $so->customer->name }}</p>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tomorrow's Service Orders --}}
                    <div class="col-lg-4 col-md-12 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title">Service Orders Besok</h3>
                            </div>
                            <div class="card-body">
                                @if($tomorrowServiceOrders->isEmpty())
                                    <p>Tidak ada Service Order untuk besok.</p>
                                @else
                                    <div class="list-group">
                                        @foreach($tomorrowServiceOrders as $so)
                                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action flex-column align-items-start">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1">{{ $so->so_number }}</h5>
                                                    <div>
                                                        <span class="badge bg-{{ $so->status == 'proses' ? 'info' : 'primary' }} text-white me-1">{{ ucfirst($so->status) }}</span>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</small>
                                                    </div>
                                                </div>
                                                <p class="mb-1">Pelanggan: {{ $so->customer->name }}</p>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Past Service Orders --}}
                    <div class="col-lg-4 col-md-12 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title">Service Orders Terlewat</h3>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                @if($pastServiceOrders->isEmpty())
                                    <p>Tidak ada Service Order dari tanggal lalu.</p>
                                @else
                                    <div class="list-group">
                                        @foreach($pastServiceOrders as $so)
                                            <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action flex-column align-items-start">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h5 class="mb-1">{{ $so->so_number }}</h5>
                                                    <div>
                                                        <span class="badge bg-{{ $so->status == 'proses' ? 'info' : 'primary' }} text-white me-1">{{ ucfirst($so->status) }}</span>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</small>
                                                    </div>
                                                </div>
                                                <p class="mb-1">Pelanggan: {{ $so->customer->name }}</p>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Di sinilah Anda akan meletakkan semua widget dashboard nanti --}}
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                Selamat Datang! Halaman dashboard Anda sudah menggunakan layout Tabler.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Cancelled Orders Modal --}}
    <div class="modal modal-blur fade" id="cancelledOrdersModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
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
                                <a href="{{ route('web.service-orders.show', $so->id) }}" class="list-group-item list-group-item-action flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">{{ $so->so_number }}</h5>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($so->work_date)->format('d M Y') }}</small>
                                    </div>
                                    <p class="mb-1">Pelanggan: {{ $so->customer->name }}</p>
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
@endsection