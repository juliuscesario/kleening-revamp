@extends('layouts.admin')
@section('title', 'Detail Service Order')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Detail Service Order: {{ $serviceOrder->so_number }}</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                @if(auth()->user()->role !== 'staff')
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editServiceOrderModal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                    Edit Service Order
                </button>
                @endif
                <a href="{{ route('web.service-orders.print', $serviceOrder->id) }}" class="btn btn-success" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                    Print Service Order
                </a>
                <a href="{{ route('web.service-orders.index') }}" class="btn">Kembali</a>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detail Pesanan</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Customer:</strong>
                            @if ($serviceOrder->customer)
                                {{ $serviceOrder->customer->name }}
                                @if ($serviceOrder->customer->trashed())
                                    <span class="badge bg-danger text-bg-secondary">Archived</span>
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                        <p><strong>Alamat:</strong>
                            @if ($serviceOrder->address)
                                {{ $serviceOrder->address->full_address }}
                                @if ($serviceOrder->address->google_maps_link)
                                    <a href="{{ $serviceOrder->address->google_maps_link }}" class="btn btn-sm btn-outline-primary ms-2" target="_blank">Buka Peta</a>
                                @endif
                                @if ($serviceOrder->address->trashed())
                                    <span class="badge bg-danger text-bg-secondary">Archived</span>
                                @endif
                            @else
                                N/A
                            @endif
                        </p>
                        <p><strong>Area:</strong>
                            @if ($serviceOrder->address && $serviceOrder->address->area)
                                {{ $serviceOrder->address->area->name }}
                            @else
                                N/A
                            @endif
                        </p>
                        <p><strong>Tanggal Pengerjaan:</strong> {{ \Carbon\Carbon::parse($serviceOrder->work_date)->format('d M Y') }}</p>
                        <p><strong>Status:</strong>
                            @php
                                $statusBadgeClass = '';
                                switch ($serviceOrder->status) {
                                    case 'booked': $statusBadgeClass = 'bg-primary'; break;
                                    case 'proses': $statusBadgeClass = 'bg-warning'; break;
                                    case 'cancelled': $statusBadgeClass = 'bg-danger'; break;
                                    case 'done': $statusBadgeClass = 'bg-success'; break;
                                    case 'invoiced': $statusBadgeClass = 'bg-secondary'; break;
                                    default: $statusBadgeClass = 'bg-secondary'; break;
                                }
                            @endphp
                            <span class="badge {{ $statusBadgeClass }} text-bg-secondary">{{ ucfirst($serviceOrder->status) }}</span>
                        </p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Layanan yang Dipesan</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap">
                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Price/Unit</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceOrder->items as $item)
                                <tr>
                                    <td>{{ $item->service->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total Keseluruhan</th>
                                    <th class="text-end">Rp {{ number_format($serviceOrder->items->sum('total'), 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Bukti Pekerjaan</h3>
                    </div>
                    <div class="card-body">
                        @if($serviceOrder->workPhotos->isNotEmpty())
                            <div class="row row-cards">
                                @foreach($serviceOrder->workPhotos as $photo)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card card-sm">
                                            <a href="{{ $photo->photo_url }}" target="_blank" class="d-block"><img src="{{ $photo->photo_url }}" class="card-img-top"></a>
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-{{ $photo->type == 'arrival' ? 'primary' : ($photo->type == 'before' ? 'info' : 'success') }} me-2">{{ ucfirst($photo->type) }}</span>
                                                    <div>
                                                        <div>{{ $photo->uploader->name ?? 'N/A' }}</div>
                                                        <div class="text-muted">{{ $photo->created_at->format('d M Y H:i') }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Belum ada bukti pekerjaan yang diunggah.</p>
                        @endif
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Tanda Tangan</h3>
                    </div>
                    <div class="card-body">
                        <div class="row row-cards">
                            @if($serviceOrder->customer_signature_image)
                                <div class="col-md-6 col-lg-4">
                                    <div class="card card-sm">
                                        <div class="card-body text-center">
                                            <p class="text-muted">Tanda Tangan Pelanggan</p>
                                            <img src="{{ $serviceOrder->customer_signature_image }}" alt="Customer Signature" style="max-width: 100%; height: auto; border: 1px solid #ccc;">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @foreach($serviceOrder->staff as $staff)
                                @if($staff->pivot->signature_image)
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card card-sm">
                                            <div class="card-body text-center">
                                                <p class="text-muted">Tanda Tangan Staff: {{ $staff->name }}</p>
                                                <img src="{{ $staff->pivot->signature_image }}" alt="Staff Signature" style="max-width: 100%; height: auto; border: 1px solid #ccc;">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if(!$serviceOrder->customer_signature_image && $serviceOrder->staff->every(fn($staff) => !$staff->pivot->signature_image))
                                <p class="text-muted">Belum ada tanda tangan.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Staff yang Bertugas</h3>
                    </div>
                    <div class="card-body">
                        @if($serviceOrder->staff->isNotEmpty())
                            <ul class="list-group list-group-flush">
                                @foreach($serviceOrder->staff as $staff)
                                    <li class="list-group-item">{{ $staff->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Belum ada staff yang ditugaskan.</p>
                        @endif
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Catatan</h3>
                    </div>
                    <div class="card-body">
                        <h5>Catatan untuk Dikerjakan</h5>
                        <p class="text-muted">{{ $serviceOrder->work_notes ?? 'Tidak ada catatan.' }}</p>
                        <h5 class="mt-3">Catatan Internal (untuk Staff)</h5>
                        <p class="text-muted">{{ $serviceOrder->staff_notes ?? 'Tidak ada catatan.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Order Modal -->
<div class="modal modal-blur fade" id="editServiceOrderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Service Order {{ $serviceOrder->so_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('pages.service-orders._edit_modal_content', ['serviceOrder' => $serviceOrder, 'allServices' => $allServices, 'allStaff' => $allStaff])
            </div>
        </div>
    </div>
</div>
@endsection
