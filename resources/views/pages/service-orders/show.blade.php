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
                    @if(in_array($serviceOrder->status, ['booked', 'proses']))
                    <form action="{{ route('web.service-orders.cancel', $serviceOrder) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan order ini?')">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                            Cancel Order
                        </button>
                    </form>
                    @elseif($serviceOrder->status === 'done' && (!$serviceOrder->invoice || $serviceOrder->invoice->status === 'cancelled'))
                    @php
                        $allSessionsDone = $serviceOrder->sessions()
                            ->where('status', '!=', 'cancel')
                            ->where('status', '!=', 'done')
                            ->doesntExist();
                    @endphp
                    @if($allSessionsDone)
                    <a href="{{ route('web.invoices.create', ['service_order_id' => $serviceOrder->id]) }}" class="btn btn-teal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-invoice" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 7l1 0" /><path d="M9 13l6 0" /><path d="M13 17l2 0" /></svg>
                        Create Invoice
                    </a>
                    @else
                    <button class="btn btn-secondary" disabled title="Selesaikan semua sesi terlebih dahulu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-invoice" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 7l1 0" /><path d="M9 13l6 0" /><path d="M13 17l2 0" /></svg>
                        Create Invoice
                    </button>
                    @endif
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
                    @if($serviceOrder->status !== 'done' && in_array(auth()->user()->role, ['admin', 'owner', 'co_owner']))
                    <form action="{{ route('web.service-orders.mark-complete', $serviceOrder) }}" method="POST" class="d-inline" onsubmit="return confirm('Tandai semua sesi dan SO sebagai selesai?')">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check"></i> Tandai Selesai
                        </button>
                    </form>
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

                {{-- Sesi Kerja (Sessions) Card --}}
                <div class="card mt-4" id="sessions-card">
                    <div class="card-header">
                        <h3 class="card-title">Sesi Kerja</h3>
                        <div class="card-actions">
                            @if(in_array(auth()->user()->role, ['admin', 'owner', 'co_owner']))
                            <button class="btn btn-sm btn-primary" id="btn-add-session">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5v14m-7-7h14"/></svg>
                                Tambah Sesi
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter text-nowrap" id="sessions-table">
                                <thead>
                                    <tr>
                                        <th style="width:40px">#</th>
                                        <th>Tanggal</th>
                                        <th style="width:70px">Jam</th>
                                        <th>Staff</th>
                                        <th style="width:80px">Type</th>
                                        <th style="width:80px">Status</th>
                                        <th>Notes</th>
                                        <th style="width:80px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="sessions-tbody">
                                    @forelse($serviceOrder->sessions as $session)
                                    <tr data-session-id="{{ $session->id }}">
                                        <td class="text-center">{{ $session->session_number }}</td>
                                        <td>
                                            @if($session->tanggal)
                                                {{ $session->tanggal->format('d M Y') }}
                                            @else
                                                <span class="text-muted">Belum dijadwalkan</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->jam)
                                                {{ \Carbon\Carbon::parse($session->jam)->format('H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->staff->isNotEmpty())
                                                {{ $session->staff->pluck('name')->implode(', ') }}
                                            @else
                                                <span class="text-muted">Belum diassign</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($session->type !== 'kerja')
                                                @php
                                                    $typeColor = match($session->type) {
                                                        'pickup' => 'warning',
                                                        'delivery' => 'success',
                                                        'survey' => 'purple',
                                                        'workshop' => 'orange',
                                                        'rework' => 'red',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $typeColor }} text-bg-secondary">{{ $session->type_label }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusColor = match($session->status) {
                                                    'done' => 'success',
                                                    'proses' => 'warning',
                                                    'cancel' => 'danger',
                                                    default => 'primary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }} text-bg-secondary{{ $session->status === 'cancel' ? ' muted' : '' }}">{{ $session->status_label }}</span>
                                        </td>
                                        <td>
                                            @if($session->notes)
                                                <span class="session-notes" data-full="{{ e($session->notes) }}">{{ Str::limit($session->notes, 30, '…') }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(in_array(auth()->user()->role, ['admin', 'owner', 'co_owner']))
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-secondary btn-edit-session"
                                                    data-session-id="{{ $session->id }}"
                                                    data-tanggal="{{ $session->tanggal ? $session->tanggal->format('Y-m-d') : '' }}"
                                                    data-jam="{{ $session->jam ? \Carbon\Carbon::parse($session->jam)->format('H:i') : '' }}"
                                                    data-type="{{ $session->type }}"
                                                    data-status="{{ $session->status }}"
                                                    data-notes="{{ $session->notes ?? '' }}"
                                                    data-staff="{{ $session->staff->pluck('id')->join(',') }}"
                                                    title="Edit Sesi">Edit</button>
                                                <button class="btn btn-sm btn-danger btn-delete-session"
                                                    data-session-id="{{ $session->id }}"
                                                    title="Hapus">Hapus</button>
                                            </div>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Belum ada sesi kerja.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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

                            @foreach($serviceOrder->sessions as $session)
                                @foreach($session->staff as $staff)
                                    @if($staff->pivot->signature_image)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card card-sm">
                                                <div class="card-body text-center">
                                                    <p class="text-muted">Tanda Tangan Staff: {{ $staff->name }} (Sesi {{ $session->session_number }})</p>
                                                    <img src="{{ $staff->pivot->signature_image }}" alt="Staff Signature" style="max-width: 100%; height: auto; border: 1px solid #ccc;">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach

                            @php
                                $hasStaffSignature = false;
                                foreach($serviceOrder->sessions as $session) {
                                    foreach($session->staff as $staff) {
                                        if ($staff->pivot->signature_image) {
                                            $hasStaffSignature = true;
                                            break;
                                        }
                                    }
                                    if ($hasStaffSignature) break;
                                }
                            @endphp
                            @if(!$serviceOrder->customer_signature_image && !$hasStaffSignature)
                                <p class="text-muted">Belum ada tanda tangan.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- FINAL ORDER (from staff submission) --}}
                @if($serviceOrder->finalOrder)
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">📋 Final Order</h3>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light rounded p-3 mb-2" style="white-space:pre-wrap; font-size:0.85rem;">{{ $serviceOrder->finalOrder->content }}</pre>
                        <small class="text-muted">
                            Disubmit oleh {{ $serviceOrder->finalOrder->submittedBy->name ?? '-' }}
                            pada {{ $serviceOrder->finalOrder->submitted_at?->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-lg-4">
                {{-- Financial Status Card --}}
                <div class="card" id="financial-status-card">
                    <div class="card-header">
                        <h3 class="card-title">Status Keuangan</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $finStatus = '';
                            $finBadgeClass = '';
                            if ($serviceOrder->invoice && $serviceOrder->invoice->status !== 'cancelled') {
                                $invStatus = $serviceOrder->invoice->status;
                                if ($invStatus === 'new') { $finStatus = 'Invoiced'; $finBadgeClass = 'bg-secondary'; }
                                elseif ($invStatus === 'sent') { $finStatus = 'Tagih'; $finBadgeClass = 'bg-primary'; }
                                elseif ($invStatus === 'overdue') { $finStatus = 'Belum Bayar'; $finBadgeClass = 'bg-danger'; }
                                elseif ($invStatus === 'paid') { $finStatus = 'Lunas'; $finBadgeClass = 'bg-success'; }
                                else { $finStatus = ucfirst($invStatus); $finBadgeClass = 'bg-muted text-dark'; }
                            } else {
                                $finStatus = ucfirst($serviceOrder->status);
                                $finBadgeClass = $serviceOrder->status === 'done' ? 'bg-success' : ($serviceOrder->status === 'proses' ? 'bg-warning' : ($serviceOrder->status === 'cancelled' ? 'bg-danger' : 'bg-secondary'));
                            }
                        @endphp
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted">Status Order</span>
                            <span class="badge {{ $finBadgeClass }} fs-5">{{ $finStatus }}</span>
                        </div>
                        <hr>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted">Total</span>
                            <strong>Rp {{ number_format($serviceOrder->items->sum('total'), 0, ',', '.') }}</strong>
                        </div>
                        @if($serviceOrder->invoice)
                        <hr>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted">Invoice</span>
                            <a href="{{ route('web.invoices.show', $serviceOrder->invoice) }}" class="btn btn-sm btn-ghost-primary">#{{ $serviceOrder->invoice->invoice_number ?? 'N/A' }}</a>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Staff yang Bertugas</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $assignedStaff = $serviceOrder->allAssignedStaff();
                        @endphp
                        @if($assignedStaff->isNotEmpty())
                            <ul class="list-group list-group-flush">
                                @foreach($assignedStaff as $staff)
                                    <li class="list-group-item">{{ $staff->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Belum ada staff yang ditugaskan.</p>
                        @endif
                        @if($serviceOrder->sessions->isNotEmpty())
                            <hr>
                            <p class="text-muted mt-2 mb-0" style="font-size:.75rem;">Staff per sesi tersedia di tabel Sesi Kerja.</p>
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
                @include('pages.service-orders._edit_modal_content', ['serviceOrder' => $serviceOrder, 'allServices' => $allServices, 'allStaff' => $allStaff, 'selectedStaffIds' => $selectedStaffIds])
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
        services: {!! json_encode($serviceOrder->items->map(function($i, $k) { return ($k + 1) . '. ' . $i->service->name . ' x ' . $i->quantity; })->join("\n")) !!}
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

    {{-- Session CRUD JS --}}
    let sessionModal;
    document.addEventListener('DOMContentLoaded', function () {
        const soId = {{ $serviceOrder->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const modalEl = document.getElementById('sessionModal');
        sessionModal = modalEl ? new bootstrap.Modal(modalEl) : null;
        @php $canManage = in_array(auth()->user()->role, ['admin', 'owner', 'co_owner']); @endphp
        const canManageSessions = @json($canManage);

        // Notes expand/collapse works for everyone
        document.getElementById('sessions-tbody').addEventListener('click', function (e) {
            const noteSpan = e.target.closest('.session-notes');
            if (noteSpan) {
                if (noteSpan.dataset.expanded === 'true') {
                    noteSpan.textContent = noteSpan.dataset.truncated;
                    noteSpan.dataset.expanded = 'false';
                } else {
                    noteSpan.dataset.truncated = noteSpan.textContent;
                    noteSpan.textContent = noteSpan.dataset.full;
                    noteSpan.dataset.expanded = 'true';
                }
            }
        });

        if (!canManageSessions) return;

        // Add button
        const addBtn = document.getElementById('btn-add-session');
        if (addBtn) {
            addBtn.addEventListener('click', function () {
                openSessionModal(null);
            });
        }

        // Edit/Delete buttons (event delegation)
        document.getElementById('sessions-tbody').addEventListener('click', function (e) {
            const editBtn = e.target.closest('.btn-edit-session');
            const deleteBtn = e.target.closest('.btn-delete-session');

            if (editBtn) {
                const sessionId = editBtn.dataset.sessionId;
                openSessionModal(parseInt(sessionId));
            }

            if (deleteBtn) {
                const sessionId = parseInt(deleteBtn.dataset.sessionId);
                deleteSession(sessionId);
            }
        });

        // Save button
        document.getElementById('btn-save-session').addEventListener('click', function () {
            saveSession();
        });

        function openSessionModal(sessionId) {
            // Reset form
            document.getElementById('session-id').value = '';
            document.getElementById('session-tanggal').value = '';
            document.getElementById('session-jam').value = '';
            document.getElementById('session-type').value = 'kerja';
            document.getElementById('session-status').value = 'booked';
            document.getElementById('session-notes').value = '';
            document.querySelectorAll('.session-staff-cb').forEach(cb => cb.checked = false);
            document.getElementById('session-notes-group').style.display = 'none';

            if (sessionId) {
                // Edit mode: read data-* attributes from edit button
                document.getElementById('sessionModalTitle').textContent = 'Edit Sesi';
                const editBtn = document.querySelector(`.btn-edit-session[data-session-id="${sessionId}"]`);
                if (!editBtn) return;

                document.getElementById('session-id').value = sessionId;

                const tanggal = editBtn.dataset.tanggal;
                if (tanggal) document.getElementById('session-tanggal').value = tanggal;

                const jam = editBtn.dataset.jam;
                if (jam && jam !== '-') document.getElementById('session-jam').value = jam;

                const type = editBtn.dataset.type;
                if (type) document.getElementById('session-type').value = type;

                const status = editBtn.dataset.status;
                if (status) document.getElementById('session-status').value = status;

                const notes = editBtn.dataset.notes;
                if (notes) document.getElementById('session-notes').value = notes;

                const staffIds = editBtn.dataset.staff;
                if (staffIds) {
                    const ids = staffIds.split(',').filter(Boolean);
                    document.querySelectorAll('.session-staff-cb').forEach(cb => {
                        if (ids.includes(cb.value)) cb.checked = true;
                    });
                }

                document.getElementById('session-notes-group').style.display = '';
            } else {
                document.getElementById('sessionModalTitle').textContent = 'Tambah Sesi';
            }

            sessionModal.show();
        }

        function saveSession() {
            const sessionId = document.getElementById('session-id').value;
            const isEdit = sessionId !== '';
            const staffIds = [];
            document.querySelectorAll('.session-staff-cb:checked').forEach(cb => {
                staffIds.push(parseInt(cb.value));
            });

            const payload = {
                tanggal: document.getElementById('session-tanggal').value || null,
                jam: document.getElementById('session-jam').value || null,
                type: document.getElementById('session-type').value,
                status: document.getElementById('session-status').value,
                notes: document.getElementById('session-notes').value || null,
                staff_ids: staffIds,
            };

            const url = isEdit ? `/sessions/${sessionId}` : `/service-orders/${soId}/sessions`;
            const method = isEdit ? 'PUT' : 'POST';

            const btnSave = document.getElementById('btn-save-session');
            btnSave.disabled = true;
            btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btnSave.disabled = false;
                btnSave.textContent = 'Simpan';

                if (data.success) {
                    sessionModal.hide();
                    if (isEdit) {
                        updateSessionRow(data.session);
                    } else {
                        appendSessionRow(data.session);
                    }
                } else {
                    alert(data.message || 'Gagal menyimpan sesi.');
                }
            })
            .catch(err => {
                btnSave.disabled = false;
                btnSave.textContent = 'Simpan';
                alert('Terjadi kesalahan saat menyimpan sesi.');
            });
        }

        function deleteSession(sessionId) {
            if (!confirm('Hapus sesi ini?')) return;

            fetch(`/sessions/${sessionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`#sessions-tbody tr[data-session-id="${sessionId}"]`);
                    if (row) row.remove();
                    renumberSessions();
                    // Check if tbody is now empty
                    const tbody = document.getElementById('sessions-tbody');
                    if (tbody.querySelectorAll('tr').length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">Belum ada sesi kerja.</td></tr>';
                    }
                } else {
                    alert(data.message || 'Gagal menghapus sesi.');
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan saat menghapus sesi.');
            });
        }

        function updateSessionRow(sessionData) {
            const row = document.querySelector(`#sessions-tbody tr[data-session-id="${sessionData.id}"]`);
            if (!row) return;

            // Update cells
            row.children[0].textContent = sessionData.session_number;
            row.children[1].innerHTML = sessionData.tanggal
                ? formatShortDate(sessionData.tanggal)
                : '<span class="text-muted">Belum dijadwalkan</span>';
            row.children[2].textContent = sessionData.jam || '-';
            row.children[3].textContent = sessionData.staff_names && sessionData.staff_names.length > 0
                ? sessionData.staff_names.join(', ')
                : '<span class="text-muted">Belum diassign</span>';

            // Type badge
            if (sessionData.type !== 'kerja') {
                const typeColors = { pickup: 'warning', delivery: 'success', survey: 'purple', workshop: 'orange' };
                const typeColor = typeColors[sessionData.type] || 'secondary';
                row.children[4].innerHTML = `<span class="badge bg-${typeColor} text-bg-secondary">${sessionData.type_label}</span>`;
            } else {
                row.children[4].innerHTML = '<span class="text-muted">—</span>';
            }

            // Status badge
            const statusColors = { done: 'success', proses: 'warning', cancel: 'danger' };
            const statusColor = statusColors[sessionData.status] || 'primary';
            const mutedClass = sessionData.status === 'cancel' ? ' muted' : '';
            row.children[5].innerHTML = `<span class="badge bg-${statusColor} text-bg-secondary${mutedClass}">${sessionData.status_label}</span>`;

            // Notes
            if (sessionData.notes) {
                const truncated = sessionData.notes.length > 30 ? sessionData.notes.substring(0, 30) + '…' : sessionData.notes;
                row.children[6].innerHTML = `<span class="session-notes" data-full="${escapeHtml(sessionData.notes)}">${truncated}</span>`;
            } else {
                row.children[6].innerHTML = '<span class="text-muted">—</span>';
            }
        }

        function appendSessionRow(sessionData) {
            const tbody = document.getElementById('sessions-tbody');
            // Remove empty message if present
            const emptyRow = tbody.querySelector('tr td[colspan]');
            if (emptyRow) emptyRow.closest('tr').remove();

            const typeColors = { pickup: 'warning', delivery: 'success', survey: 'purple', workshop: 'orange' };
            const typeColor = sessionData.type !== 'kerja' ? (typeColors[sessionData.type] || 'secondary') : null;
            const statusColors = { done: 'success', proses: 'warning', cancel: 'danger' };
            const statusColor = statusColors[sessionData.status] || 'primary';
            const mutedClass = sessionData.status === 'cancel' ? ' muted' : '';
            const notesHtml = sessionData.notes
                ? `<span class="session-notes" data-full="${escapeHtml(sessionData.notes)}">${sessionData.notes.length > 30 ? sessionData.notes.substring(0, 30) + '…' : sessionData.notes}</span>`
                : '<span class="text-muted">—</span>';

            const tr = document.createElement('tr');
            tr.dataset.sessionId = sessionData.id;
            const staffAttr = sessionData.staff_ids && sessionData.staff_ids.length > 0
                ? `data-staff="${sessionData.staff_ids.join(',')}"`
                : 'data-staff=""';
            tr.innerHTML = `
                <td class="text-center">${sessionData.session_number}</td>
                <td>${sessionData.tanggal ? formatShortDate(sessionData.tanggal) : '<span class="text-muted">Belum dijadwalkan</span>'}</td>
                <td>${sessionData.jam || '-'}</td>
                <td>${sessionData.staff_names && sessionData.staff_names.length > 0 ? sessionData.staff_names.join(', ') : '<span class="text-muted">Belum diassign</span>'}</td>
                <td class="text-center">${typeColor ? `<span class="badge bg-${typeColor} text-bg-secondary">${sessionData.type_label}</span>` : '<span class="text-muted">—</span>'}</td>
                <td class="text-center"><span class="badge bg-${statusColor} text-bg-secondary${mutedClass}">${sessionData.status_label}</span></td>
                <td>${notesHtml}</td>
                <td>
                    ${canManageSessions ? `
                    <div class="btn-group">
                        <button class="btn btn-sm btn-secondary btn-edit-session"
                            data-session-id="${sessionData.id}"
                            data-tanggal="${sessionData.tanggal || ''}"
                            data-jam="${sessionData.jam || ''}"
                            data-type="${sessionData.type}"
                            data-status="${sessionData.status}"
                            data-notes="${sessionData.notes ? escapeHtml(sessionData.notes) : ''}"
                            ${staffAttr}
                            title="Edit Sesi">Edit</button>
                        <button class="btn btn-sm btn-danger btn-delete-session"
                            data-session-id="${sessionData.id}"
                            title="Hapus">Hapus</button>
                    </div>
                    ` : '<span class="text-muted">—</span>'}
                </td>
            `;
            tbody.appendChild(tr);
        }

        function renumberSessions() {
            const rows = document.querySelectorAll('#sessions-tbody tr[data-session-id]');
            rows.forEach((row, idx) => {
                row.children[0].textContent = idx + 1;
            });
        }

        function formatShortDate(dateStr) {
            // "2026-05-05" → "05 Mei 2026"
            const parts = dateStr.split('-');
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]) - 1;
            const day = parseInt(parts[2]);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${day.toString().padStart(2, '0')} ${months[month]} ${year}`;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
    });
</script>

{{-- Session Modal --}}
<div class="modal modal-blur fade" id="sessionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionModalTitle">Tambah Sesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="session-form">
                    <input type="hidden" id="session-id" value="">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="session-tanggal">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Jam</label>
                            <input type="time" class="form-control" id="session-jam">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label required">Type</label>
                            <select class="form-select" id="session-type" required>
                                <option value="kerja">Kerja</option>
                                <option value="pickup">Pickup</option>
                                <option value="delivery">Delivery</option>
                                <option value="survey">Survey</option>
                                <option value="workshop">Workshop</option>
                                <option value="rework">Rework</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="session-status">
                                <option value="booked">Booked</option>
                                <option value="proses">Proses</option>
                                <option value="done">Done</option>
                                <option value="cancel">Cancel</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Staff</label>
                            <div class="row g-2" id="session-staff-checkboxes">
                                @foreach($allStaff as $staffMember)
                                <div class="col-6 col-sm-4">
                                    <label class="form-check mb-0">
                                        <input class="form-check-input session-staff-cb" type="checkbox" value="{{ $staffMember->id }}">
                                        <span class="form-check-label">{{ $staffMember->name }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12" id="session-notes-group" style="display:none;">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="session-notes" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-save-session">Simpan</button>
            </div>
        </div>
    </div>
</div>

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
</script>
@endsection
