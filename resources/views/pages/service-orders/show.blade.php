@extends('layouts.admin')
@section('title', 'Detail Service Order')

@section('content')
<div id="service-order-show-page" style="display:none;"></div>
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Detail Service Order: {{ $serviceOrder->so_number }}</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    @if($serviceOrder->status === 'done' && (!$serviceOrder->invoice || $serviceOrder->invoice->status === 'cancelled'))
                    <a href="{{ route('web.invoices.create', ['service_order_id' => $serviceOrder->id]) }}" class="btn btn-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-invoice" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 7l1 0" /><path d="M9 13l6 0" /><path d="M13 17l2 0" /></svg>
                        Create Invoice
                    </a>
                    @endif
                    <button id="btn-confirm-order" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        Confirm Order
                    </button>
                    @if(auth()->user()->role !== 'staff')
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editServiceOrderModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                        Edit Service Order
                    </button>
                    @endif
                    @if($serviceOrder->status === 'booked')
                    <button class="btn btn-primary" onclick="updateStatus({{ $serviceOrder->id }}, 'proses')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-play" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 4v16l13 -8z" /></svg>
                        Proses
                    </button>
                    @elseif($serviceOrder->status === 'proses')
                    <button class="btn btn-success" onclick="updateStatus({{ $serviceOrder->id }}, 'done')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-circle-check" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>
                        Done
                    </button>
                    @endif
                    <a href="{{ route('web.service-orders.index') }}" class="btn">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        @if (session('success'))
            <div class="alert alert-success mb-3" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mb-3" role="alert">
                {{ session('error') }}
            </div>
        @endif
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
                        <p><strong>Nomor Handphone:</strong>
                            @if ($serviceOrder->customer && $serviceOrder->customer->phone_number)
                                <a href="https://wa.me/{{ $serviceOrder->customer->phone_number }}">{{ $serviceOrder->customer->phone_number }}</a>
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
                        <p><strong>Waktu Pengerjaan (WIB):</strong> {{ $serviceOrder->work_time_formatted ? $serviceOrder->work_time_formatted . ' WIB' : 'Tidak diatur' }}</p>
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
                        <h3 class="card-title">Foto Pekerjaan</h3>
                    </div>
                    <div class="card-body">
                        @if(in_array(auth()->user()->role, ['admin', 'owner', 'co_owner']))
                            <div class="row row-cards">
                                {{-- Arrival Photo --}}
                                <div class="col-md-4">
                                    @include('pages.service-orders._photo_card', [
                                        'type' => 'arrival',
                                        'label' => 'Arrival',
                                        'badgeClass' => 'primary',
                                        'photo' => $workPhotos->get('arrival'),
                                        'serviceOrderId' => $serviceOrder->id
                                    ])
                                </div>
                                {{-- Before Photo --}}
                                <div class="col-md-4">
                                    @include('pages.service-orders._photo_card', [
                                        'type' => 'before',
                                        'label' => 'Before',
                                        'badgeClass' => 'info',
                                        'photo' => $workPhotos->get('before'),
                                        'serviceOrderId' => $serviceOrder->id
                                    ])
                                </div>
                                {{-- After Photo --}}
                                <div class="col-md-4">
                                    @include('pages.service-orders._photo_card', [
                                        'type' => 'after',
                                        'label' => 'After',
                                        'badgeClass' => 'success',
                                        'photo' => $workPhotos->get('after'),
                                        'serviceOrderId' => $serviceOrder->id
                                    ])
                                </div>
                            </div>
                        @else
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
                        <h5>Catatan Invoice</h5>
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

<script>
    window.soConfirmData = {
        name: "{{ $serviceOrder->customer->name ?? '' }}",
        phone: "{{ $serviceOrder->customer->phone_number ?? '' }}",
        tanggal: "{{ $serviceOrder->work_date ? \Carbon\Carbon::parse($serviceOrder->work_date)->translatedFormat('d F Y') : '' }}",
        jam: "{{ $serviceOrder->work_time_formatted ?? '' }}",
        alamat: "{{ $serviceOrder->address ? addslashes($serviceOrder->address->full_address) : '' }}",
        services: {!! json_encode($serviceOrder->items->map(fn($i, $k) => ($k + 1) . '. ' . $i->service->name . ' x ' . $i->quantity)->join("\n")) !!}
    };

    @if(in_array(auth()->user()->role, ['admin', 'owner', 'co_owner']))
    document.addEventListener('DOMContentLoaded', function () {
        const soId = {{ $serviceOrder->id }};

        // Upload button click — trigger hidden file input
        document.querySelectorAll('.btn-upload').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const type = this.dataset.type;
                const input = document.querySelector('.photo-input[data-type="' + type + '"]');
                input.click();
            });
        });

        // Replace button click — trigger hidden file input
        document.querySelectorAll('.btn-replace').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const type = this.dataset.type;
                const input = document.querySelector('.photo-input[data-type="' + type + '"]');
                input.click();
            });
        });

        // File input change — upload via AJAX
        document.querySelectorAll('.photo-input').forEach(function (input) {
            input.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                const type = this.dataset.type;
                const formData = new FormData();
                formData.append('photo', file);
                formData.append('type', type);

                const btn = document.querySelector('.btn-upload[data-type="' + type + '"], .btn-replace[data-type="' + type + '"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Uploading...';
                }

                fetch('/service-orders/' + soId + '/photos', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: formData
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Gagal mengupload foto.');
                        if (btn) {
                            btn.disabled = false;
                            const type = btn.dataset.type;
                            const isReplace = btn.classList.contains('btn-replace');
                            btn.innerHTML = isReplace
                                ? '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><polyline points="7 11 12 6 17 11" /><line x1="12" y1="6" x2="12" y2="18" /></svg> Ganti'
                                : '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-camera" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2z" /><circle cx="12" cy="13" r="3" /></svg> Upload';
                        }
                    }
                })
                .catch(function (err) {
                    alert('Terjadi kesalahan saat mengupload foto.');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'Upload';
                    }
                });
            });
        });

        // Delete button click
        document.querySelectorAll('.btn-delete-photo').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm('Hapus foto ini?')) return;

                const photoId = this.dataset.photoId;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';

                fetch('/service-orders/' + soId + '/photos/' + photoId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Gagal menghapus foto.');
                        btn.disabled = false;
                        btn.innerHTML = 'Hapus';
                    }
                })
                .catch(function (err) {
                    alert('Terjadi kesalahan saat menghapus foto.');
                    btn.disabled = false;
                    btn.innerHTML = 'Hapus';
                });
            });
        });
    });
    @endif
</script>

{{-- Work Photos Lightbox --}}
<div id="photo-lightbox" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:9999; align-items:center; justify-content:center;" onclick="closeLightbox()">
    <img id="lightbox-img" src="" style="max-width:95vw; max-height:95vh; object-fit:contain; border-radius:8px;">
</div>
<script>
function openLightbox(url) {
    document.getElementById('lightbox-img').src = url;
    document.getElementById('photo-lightbox').style.display = 'flex';
}
function closeLightbox() {
    document.getElementById('photo-lightbox').style.display = 'none';
}

function updateStatus(soId, newStatus) {
    if (!confirm('Ubah status ke "' + newStatus + '"?')) return;

    fetch(`/service-orders/${soId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Gagal mengubah status.');
        }
    })
    .catch(() => alert('Terjadi kesalahan.'));
}
</script>
@endsection
