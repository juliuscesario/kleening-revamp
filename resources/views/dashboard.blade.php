@extends('layouts.admin')

@section('title', 'Dashboard Utama')

@section('content')
    <div class="page-body">
        <div class="container-xl">

            @if(in_array(Auth::user()->role, ['owner', 'co_owner']))
                {{-- Owner & Co-owner Dashboard --}}
                @include('partials.dashboard.owner-coowner')
            @elseif(Auth::user()->role === 'admin')
                {{-- Admin Dashboard --}}
                @include('partials.dashboard.admin')
            @elseif(Auth::user()->role === 'staff')
                {{-- Staff Dashboard --}}
                @include('partials.dashboard.staff')
            @endif

        </div>
    </div>

    {{-- Done Orders Modal --}}
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
    
    {{-- Cancelled Orders Modal --}}
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