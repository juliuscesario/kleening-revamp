@extends('layouts.admin')
@section('title', 'Detail Service Order')

@section('content')
@php
    $finalOrder = $serviceOrder->finalOrder;
@endphp
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Detail Service Order: {{ $serviceOrder->so_number }}</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <button type="button" class="btn btn-warning text-dark" onclick="window.location.reload();">
                        Muat Ulang
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSalinOrder">
                        📋 Salin Detail Order
                    </button>
                    @if($serviceOrder->status == 'booked')
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startWorkModal"
                            @if(!$hasMesinPergi) disabled title="Upload Mesin Pergi dulu sebelum mulai kerjaan" @endif
                            id="btn-start-work">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 13.572l-7.5 7.428l-7.5 -7.428m0 0a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /><path d="M12 7l-2 4l2 4l2 -4l-2 -4" /></svg>
                            Mulai Kerja
                        </button>
                    @elseif($serviceOrder->status == 'proses' && !$serviceOrder->work_proof_completed_at)
                        @php
                            $hasBeforePhoto = $serviceOrder->workPhotos()->where('type', 'before')->exists();
                            $hasAfterPhoto = $serviceOrder->workPhotos()->where('type', 'after')->exists();
                            $hasSignature = $serviceOrder->workPhotos()->where('type', 'signature')->exists();
                        @endphp
                        @if(!$hasBeforePhoto)
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#uploadBeforeWorkProofModal"
                                @if(!$hasMesinPergi) disabled title="Upload Mesin Pergi dulu sebelum mulai kerjaan" @endif
                                id="btn-upload-before">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5" /><path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l3 3" /><path d="M14 14l1 -1c.699 -.67 1.78 -.825 2.5 -.288" /><path d="M19 22v-6" /><path d="M22 19l-3 -3l-3 3" /></svg>
                                Upload Foto Sebelum
                            </button>
                        @elseif(!$hasAfterPhoto)
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#uploadAfterWorkProofModal"
                                @if(!$hasMesinPergi) disabled title="Upload Mesin Pergi dulu sebelum mulai kerjaan" @endif
                                id="btn-upload-after">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5" /><path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l3 3" /><path d="M14 14l1 -1c.699 -.67 1.78 -.825 2.5 -.288" /><path d="M19 22v-6" /><path d="M22 19l-3 -3l-3 3" /></svg>
                                Upload Foto Sesudah
                            </button>
                        @elseif(!$finalOrder)
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#finalOrderModal">
                                📋 Final Order
                            </button>
                        @elseif(!$hasSignature)
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#signatureModal">
                                ✍️ TTD Customer
                            </button>
                        @else
                            <div class="alert alert-success mb-0">Pekerjaan selesai.</div>
                        @endif
                    @elseif($serviceOrder->status == 'proses' && $serviceOrder->work_proof_completed_at)
                        @php
                            $hasSignatureProof = $serviceOrder->workPhotos()->where('type', 'signature')->exists();
                        @endphp
                        @if(!$finalOrder)
                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#finalOrderModal">
                                📋 Final Order
                            </button>
                        @elseif(!$hasSignatureProof)
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#signatureModal">
                                ✍️ TTD Customer
                            </button>
                        @else
                            <div class="alert alert-success mb-0">Pekerjaan selesai.</div>
                        @endif
                    @elseif(in_array($serviceOrder->status, ['done', 'invoiced', 'tagih', 'blm_bayar', 'lunas']))
                        <div class="alert alert-success mb-0">Pekerjaan selesai.</div>
                    @endif
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card d-lg-none">
                    <div class="card-header">
                        <h3 class="card-title">Staff yang Bertugas</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $workSessions = $serviceOrder->sessions->where('type', 'kerja');
                        @endphp
                        @forelse($workSessions as $session)
                            <div class="mb-2">
                                <span class="text-muted">Sesi {{ $session->session_number }} ({{ $session->status_label }}):</span>
                                {{ $session->staff->pluck('name')->join(', ') ?: '-' }}
                            </div>
                        @empty
                            <p class="text-muted">Belum ada staff yang ditugaskan.</p>
                        @endforelse
                    </div>
                </div>

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
                        <p><strong>Nomor Handphone:</strong>
                            @if ($serviceOrder->customer && $serviceOrder->customer->phone_number)
                                @php
                                    $normalizedPhone = preg_replace('/\D+/', '', $serviceOrder->customer->phone_number);
                                @endphp
                                <a href="https://wa.me/{{ $serviceOrder->customer->phone_number }}">{{ $serviceOrder->customer->phone_number }}</a>
                                @if ($normalizedPhone)
                                    <a href="https://wa.me/{{ $normalizedPhone }}" class="btn btn-sm btn-outline-success ms-2" target="_blank" rel="noopener">
                                        Hubungi via WhatsApp
                                    </a>
                                @endif
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
                        <p><strong>Ditugaskan Oleh:</strong> {{ $serviceOrder->creator->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="card mt-4 d-lg-none">
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
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($serviceOrder->items as $item)
                                <tr>
                                    <td>{{ $item->service->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @php
                    $today = now()->toDateString();
                    $todaySession = $serviceOrder->sessions
                        ->filter(fn($s) => $s->tanggal && $s->tanggal->toDateString() === $today)
                        ->first();

                    $workPhotos     = $serviceOrder->workPhotos->keyBy('type');
                    $arrivalProof   = $workPhotos->get('arrival');
                    $beforeProof    = $workPhotos->get('before');
                    $afterProof     = $workPhotos->get('after');
                    $signatureProof = $workPhotos->get('signature');

                    $canSign = $afterProof && $todaySession && $todaySession->status === 'proses';
                    $isDone  = $todaySession && $todaySession->status === 'done';

                    $isLocked   = $serviceOrder->invoice &&
                                  $serviceOrder->invoice->payment_status !== 'unpaid';
                @endphp

                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Foto Dokumentasi</h5>

                        <div class="row g-3">

                            {{-- ARRIVAL --}}
                            <div class="col-6">
                                <p class="text-muted small mb-1 fw-semibold">📸 Arrival</p>
                                @if($arrivalProof)
                                    <a href="{{ Storage::url($arrivalProof->file_path) }}" target="_blank">
                                        <img src="{{ Storage::url($arrivalProof->file_path) }}"
                                             class="img-fluid rounded w-100"
                                             style="object-fit:cover; height:140px;"
                                             alt="Foto Arrival">
                                    </a>
                                @else
                                    <div class="rounded bg-secondary-lt d-flex align-items-center justify-content-center"
                                         style="height:140px;">
                                        <span class="text-muted small">Belum diupload</span>
                                    </div>
                                @endif
                            </div>

                            {{-- BEFORE --}}
                            <div class="col-6">
                                <p class="text-muted small mb-1 fw-semibold">📸 Before</p>
                                @if($beforeProof)
                                    <a href="{{ Storage::url($beforeProof->file_path) }}" target="_blank">
                                        <img src="{{ Storage::url($beforeProof->file_path) }}"
                                             class="img-fluid rounded w-100"
                                             style="object-fit:cover; height:140px;"
                                             alt="Foto Before">
                                    </a>
                                @else
                                    <div class="rounded bg-secondary-lt d-flex align-items-center justify-content-center"
                                         style="height:140px;">
                                        <span class="text-muted small">Belum diupload</span>
                                    </div>
                                @endif
                            </div>

                            {{-- AFTER --}}
                            <div class="col-6">
                                <p class="text-muted small mb-1 fw-semibold">📸 After</p>
                                @if($afterProof)
                                    <a href="{{ Storage::url($afterProof->file_path) }}" target="_blank">
                                        <img src="{{ Storage::url($afterProof->file_path) }}"
                                             class="img-fluid rounded w-100"
                                             style="object-fit:cover; height:140px;"
                                             alt="Foto After">
                                    </a>
                                @else
                                    <div class="rounded bg-secondary-lt d-flex align-items-center justify-content-center"
                                         style="height:140px;">
                                        <span class="text-muted small">Belum diupload</span>
                                    </div>
                                @endif
                            </div>

                            {{-- SIGNATURE --}}
                            <div class="col-6">
                                <p class="text-muted small mb-1 fw-semibold">✍️ TTD Customer</p>
                                @if($signatureProof)
                                    <a href="{{ Storage::url($signatureProof->file_path) }}" target="_blank">
                                        <img src="{{ Storage::url($signatureProof->file_path) }}"
                                             class="img-fluid rounded w-100"
                                             style="object-fit:contain; height:140px; background:#fff;"
                                             alt="Tanda Tangan">
                                    </a>
                                @else
                                    <div class="rounded bg-secondary-lt d-flex align-items-center justify-content-center"
                                         style="height:140px;">
                                        <span class="text-muted small">Belum ada TTD</span>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                {{-- FINAL ORDER CARD --}}
                @if($finalOrder)
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="card-title mb-0">📋 Final Order</h5>
                            @if(!$isLocked)
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#finalOrderModal">
                                    ✏️ Edit
                                </button>
                            @else
                                <span class="badge bg-secondary">🔒 Terkunci</span>
                            @endif
                        </div>
                        <pre class="bg-light rounded p-3 mb-0" style="white-space:pre-wrap; font-size:0.85rem;">{{ $finalOrder->content }}</pre>
                        <small class="text-muted mt-2 d-block">
                            Disubmit oleh {{ $finalOrder->submittedBy->name ?? '-' }} pada {{ $finalOrder->submitted_at?->format('d M Y H:i') }}
                        </small>
                    </div>
                </div>
                @endif

            </div>
            <div class="col-lg-4 d-none d-lg-block">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Staff yang Bertugas</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $workSessions = $serviceOrder->sessions->where('type', 'kerja');
                        @endphp
                        @forelse($workSessions as $session)
                            <div class="mb-2">
                                <span class="text-muted">Sesi {{ $session->session_number }} ({{ $session->status_label }}):</span>
                                {{ $session->staff->pluck('name')->join(', ') ?: '-' }}
                            </div>
                        @empty
                            <p class="text-muted">Belum ada staff yang ditugaskan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card mt-4 catatan-card">
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

<!-- Start Work Modal -->
<div class="modal modal-blur fade" id="startWorkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="startWorkForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Mulai Kerja untuk Service Order {{ $serviceOrder->so_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unggah Foto Pekerjaan</label>
                        <input type="file" class="d-none" id="photo" name="photo" accept="image/*" required>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" id="startWorkTakePhotoBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" /><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                Ambil Foto
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="startWorkSelectGalleryBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l2 2" /></svg>
                                Pilih dari Galeri
                            </button>
                        </div>
                    </div>
                    <div class="mt-2" id="photoPreview" style="display: none;">
                        <img src="" alt="Photo Preview" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <div class="d-flex align-items-center mt-3 d-none" id="startWorkLoading">
                        <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                        <span>Sedang memproses dan mengunggah foto, mohon tunggu...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Before Work Proof Modal -->
<div class="modal modal-blur fade" id="uploadBeforeWorkProofModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="uploadBeforeWorkProofForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Foto Sebelum Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unggah Foto Sebelum</label>
                        <input type="file" class="d-none" id="before_photo" name="photo" accept="image/*" required>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" id="beforeTakePhotoBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" /><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                Ambil Foto
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="beforeSelectGalleryBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l2 2" /></svg>
                                Pilih dari Galeri
                            </button>
                        </div>
                        <div class="mt-2" id="beforePhotoPreview" style="display: none;">
                            <img src="" alt="Before Photo Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3 d-none" id="beforeWorkProofLoading">
                        <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                        <span>Sedang memproses dan mengunggah foto, mohon tunggu...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload After Work Proof Modal -->
<div class="modal modal-blur fade" id="uploadAfterWorkProofModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="uploadAfterWorkProofForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Foto Sesudah Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unggah Foto Sesudah</label>
                        <input type="file" class="d-none" id="after_photo" name="photo" accept="image/*" required>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary" id="afterTakePhotoBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" /><path d="M9 13a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                                Ambil Foto
                            </button>
                            <button type="button" class="btn btn-outline-primary" id="afterSelectGalleryBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l2 2" /></svg>
                                Pilih dari Galeri
                            </button>
                        </div>
                        <div class="mt-2" id="afterPhotoPreview" style="display: none;">
                            <img src="" alt="After Photo Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                     <div class="d-flex align-items-center mt-3 d-none" id="afterWorkProofLoading">
                        <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                        <span>Sedang memproses dan mengunggah foto, mohon tunggu...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Signature Modal -->
<div class="modal modal-blur fade" id="signatureModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tanda Tangan Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Minta customer menandatangani di bawah ini.</p>
                <canvas id="signature-pad" style="border:1px solid #ccc; border-radius:4px; width:100%; height:220px; touch-action:none; background:#fff;"></canvas>
                <button type="button" id="clear-sig" class="btn btn-sm btn-outline-secondary mt-2">
                    Ulangi
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-sig" class="btn btn-primary w-100">
                    Simpan &amp; Selesaikan Pekerjaan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- FINAL ORDER MODAL --}}
<div class="modal fade" id="finalOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📋 Final Order Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Salin detail order lalu edit sesuai layanan aktual yang dikerjakan. Hapus atau tambah layanan sesuai kebutuhan.</p>
                <textarea id="finalOrderContent" class="form-control font-monospace"
                          rows="12" placeholder="Tempel detail order di sini, lalu edit sesuai kebutuhan...">{{ $finalOrder?->content }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitFinalOrder">
                    💾 Simpan Final Order
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.3/dist/heic2any.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const serviceOrderId = {{ $serviceOrder->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const authToken = localStorage.getItem('auth_token') || '';

        const MAX_FILE_SIZE_BYTES = 128000 * 1024; // 128 MB in bytes
        const ALLOWED_MIME_TYPES = new Set([
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/gif',
            'image/svg+xml',
            'image/bmp',
            'image/webp',
            'image/heic',
            'image/heif',
            'image/heic-sequence',
            'image/heif-sequence',
        ]);
        const ALLOWED_EXTENSIONS = new Set(['jpeg', 'png', 'jpg', 'gif', 'svg', 'bmp', 'webp', 'heic', 'heif']);
        const HEIC_MIME_TYPES = new Set(['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence']);
        const HEIC_EXTENSIONS = new Set(['heic', 'heif']);

        function getFileExtension(fileName) {
            if (!fileName) {
                return '';
            }
            const parts = fileName.split('.');
            return parts.length > 1 ? parts.pop().toLowerCase() : '';
        }

        function getBaseFileName(fileName) {
            if (!fileName) {
                return 'photo';
            }
            const lastDotIndex = fileName.lastIndexOf('.');
            if (lastDotIndex === -1) {
                return fileName;
            }
            return fileName.substring(0, lastDotIndex);
        }

        function hidePreview(wrapper, img) {
            if (wrapper) {
                wrapper.style.display = 'none';
            }
            if (img) {
                img.src = '';
            }
        }

        function revokePreview(input) {
            if (input && input._previewUrl) {
                URL.revokeObjectURL(input._previewUrl);
                input._previewUrl = null;
            }
        }

        function resetFileInput(input, wrapper, img) {
            if (!input) {
                return;
            }
            revokePreview(input);
            input._processedFile = null;
            if (wrapper || img) {
                hidePreview(wrapper, img);
            }
            input.value = '';
        }

        async function prepareImageFile(file) {
            if (!file) {
                throw new Error('File tidak ditemukan.');
            }

            const mimeType = (file.type || '').toLowerCase();
            const extension = getFileExtension(file.name);

            const isAllowedByMime = mimeType ? ALLOWED_MIME_TYPES.has(mimeType) : false;
            const isAllowedByExtension = ALLOWED_EXTENSIONS.has(extension);
            if (!isAllowedByMime && !isAllowedByExtension) {
                throw new Error('Format file tidak didukung. Gunakan jpeg, png, jpg, gif, svg, bmp, webp, heic, atau heif.');
            }

            if (file.size > MAX_FILE_SIZE_BYTES) {
                throw new Error('Ukuran file melebihi batas 128MB.');
            }

            const shouldConvertHeic = HEIC_MIME_TYPES.has(mimeType) || HEIC_EXTENSIONS.has(extension);
            if (shouldConvertHeic) {
                if (typeof heic2any !== 'function') {
                    throw new Error('Konversi HEIC tidak tersedia di browser ini.');
                }
                const conversionResult = await heic2any({
                    blob: file,
                    toType: 'image/jpeg',
                    quality: 0.9,
                });
                const convertedBlob = Array.isArray(conversionResult) ? conversionResult[0] : conversionResult;
                const baseName = getBaseFileName(file.name) || 'photo';
                return new File([convertedBlob], `${baseName}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });
            }

            return file;
        }

        async function handleImageSelection(input, wrapper, img) {
            if (!input) {
                return;
            }
            revokePreview(input);
            input._processedFile = null;

            const file = input.files && input.files[0] ? input.files[0] : null;

            if (!file) {
                if (wrapper || img) {
                    hidePreview(wrapper, img);
                }
                return;
            }

            try {
                const processedFile = await prepareImageFile(file);
                input._processedFile = processedFile;

                if (wrapper && img) {
                    const previewSource = processedFile.type.startsWith('image/') ? processedFile : file;
                    const previewUrl = URL.createObjectURL(previewSource);
                    input._previewUrl = previewUrl;
                    img.src = previewUrl;
                    wrapper.style.display = 'block';
                }
            } catch (error) {
                if (wrapper || img) {
                    hidePreview(wrapper, img);
                }
                alert(error.message || 'File tidak valid.');
                input.value = '';
            }
        }

        async function ensureProcessedFile(input) {
            if (!input) {
                return null;
            }

            if (input._processedFile) {
                return input._processedFile;
            }

            const file = input.files && input.files[0] ? input.files[0] : null;
            if (!file) {
                return null;
            }

            try {
                const processedFile = await prepareImageFile(file);
                input._processedFile = processedFile;
                return processedFile;
            } catch (error) {
                alert(error.message || 'File tidak valid.');
                return null;
            }
        }

        function buildAuthHeaders() {
            const headers = {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            };
            if (authToken) {
                headers['Authorization'] = 'Bearer ' + authToken;
            }
            return headers;
        }

        async function sendFormData(url, formData) {
            const response = await fetch(url, {
                method: 'POST',
                headers: buildAuthHeaders(),
                body: formData,
            });

            let data = null;
            try {
                data = await response.json();
            } catch (error) {
                // ignore JSON parse errors
            }

            if (!response.ok || (data && data.success === false)) {
                const message = data && data.message ? data.message : 'Permintaan gagal diproses.';
                throw new Error(message);
            }

            return data || {};
        }

        function setupPhotoButtons(takeBtnId, galleryBtnId, inputId) {
            const takeBtn = document.getElementById(takeBtnId);
            const galleryBtn = document.getElementById(galleryBtnId);
            const input = document.getElementById(inputId);

            if (takeBtn && galleryBtn && input) {
                takeBtn.addEventListener('click', () => {
                    input.setAttribute('capture', 'environment');
                    input.click();
                });
                galleryBtn.addEventListener('click', () => {
                    input.removeAttribute('capture');
                    input.click();
                });
            }
        }

        setupPhotoButtons('startWorkTakePhotoBtn', 'startWorkSelectGalleryBtn', 'photo');
        setupPhotoButtons('beforeTakePhotoBtn', 'beforeSelectGalleryBtn', 'before_photo');
        setupPhotoButtons('afterTakePhotoBtn', 'afterSelectGalleryBtn', 'after_photo');

        const startWorkForm = document.getElementById('startWorkForm');
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photoPreview');
        const photoPreviewImg = photoPreview ? photoPreview.querySelector('img') : null;
        const startWorkLoading = document.getElementById('startWorkLoading');
        const startWorkSubmitBtn = startWorkForm ? startWorkForm.querySelector('button[type="submit"]') : null;

        function toggleStartWorkLoading(isLoading) {
            if (startWorkLoading) {
                startWorkLoading.classList.toggle('d-none', !isLoading);
            }
            if (startWorkSubmitBtn) {
                startWorkSubmitBtn.disabled = isLoading;
            }
        }

        if (photoInput) {
            photoInput.addEventListener('change', function () {
                handleImageSelection(photoInput, photoPreview, photoPreviewImg);
            });
        }

        const startWorkModalEl = document.getElementById('startWorkModal');
        if (startWorkModalEl) {
            startWorkModalEl.addEventListener('hidden.bs.modal', function () {
                toggleStartWorkLoading(false);
                resetFileInput(photoInput, photoPreview, photoPreviewImg);
            });
        }

        if (startWorkForm && photoInput) {
            startWorkForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                toggleStartWorkLoading(true);
                try {
                    const processedFile = await ensureProcessedFile(photoInput);
                    if (!processedFile) {
                        if (!photoInput.files || photoInput.files.length === 0) {
                            alert('Mohon unggah foto terlebih dahulu.');
                        }
                        toggleStartWorkLoading(false);
                        return;
                    }

                    const formData = new FormData();
                    formData.append('photo', processedFile);

                    const data = await sendFormData(`/api/service-orders/${serviceOrderId}/start-work`, formData);
                    alert(data.message || 'Work started and photo uploaded successfully.');
                    location.reload();
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Terjadi kesalahan saat memulai pekerjaan.');
                } finally {
                    toggleStartWorkLoading(false);
                }
            });
        }

        // --- Upload Before Work Proof ---
        const uploadBeforeWorkProofModalEl = document.getElementById('uploadBeforeWorkProofModal');
        const uploadBeforeWorkProofForm = document.getElementById('uploadBeforeWorkProofForm');
        const beforePhotoInput = document.getElementById('before_photo');
        const beforePhotoPreview = document.getElementById('beforePhotoPreview');
        const beforePhotoPreviewImg = beforePhotoPreview ? beforePhotoPreview.querySelector('img') : null;
        const beforeWorkProofLoading = document.getElementById('beforeWorkProofLoading');
        const beforeWorkProofSubmitBtn = uploadBeforeWorkProofForm ? uploadBeforeWorkProofForm.querySelector('button[type="submit"]') : null;

        function toggleBeforeWorkProofLoading(isLoading) {
            if (beforeWorkProofLoading) beforeWorkProofLoading.classList.toggle('d-none', !isLoading);
            if (beforeWorkProofSubmitBtn) beforeWorkProofSubmitBtn.disabled = isLoading;
            if (beforePhotoInput) beforePhotoInput.disabled = isLoading;
        }

        if (beforePhotoInput) {
            beforePhotoInput.addEventListener('change', () => handleImageSelection(beforePhotoInput, beforePhotoPreview, beforePhotoPreviewImg));
        }

        if (uploadBeforeWorkProofModalEl) {
            uploadBeforeWorkProofModalEl.addEventListener('hidden.bs.modal', () => {
                toggleBeforeWorkProofLoading(false);
                resetFileInput(beforePhotoInput, beforePhotoPreview, beforePhotoPreviewImg);
            });
        }

        if (uploadBeforeWorkProofForm) {
            uploadBeforeWorkProofForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                toggleBeforeWorkProofLoading(true);
                try {
                    const processedFile = await ensureProcessedFile(beforePhotoInput);
                    if (!processedFile) {
                        if (!beforePhotoInput.files || beforePhotoInput.files.length === 0) {
                            alert('Mohon unggah foto terlebih dahulu.');
                        }
                        toggleBeforeWorkProofLoading(false);
                        return;
                    }

                    const formData = new FormData();
                    formData.append('type', 'before');
                    formData.append('photo', processedFile);

                    const data = await sendFormData(`/api/service-orders/${serviceOrderId}/upload-work-proof`, formData);
                    alert(data.message || 'Foto "sebelum" berhasil diunggah.');
                    location.reload();
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Gagal mengunggah foto "sebelum".');
                    toggleBeforeWorkProofLoading(false);
                }
            });
        }

        // --- Upload After Work Proof ---
        const uploadAfterWorkProofModalEl = document.getElementById('uploadAfterWorkProofModal');
        const uploadAfterWorkProofForm = document.getElementById('uploadAfterWorkProofForm');
        const afterPhotoInput = document.getElementById('after_photo');
        const afterPhotoPreview = document.getElementById('afterPhotoPreview');
        const afterPhotoPreviewImg = afterPhotoPreview ? afterPhotoPreview.querySelector('img') : null;
        const afterWorkProofLoading = document.getElementById('afterWorkProofLoading');
        const afterWorkProofSubmitBtn = uploadAfterWorkProofForm ? uploadAfterWorkProofForm.querySelector('button[type="submit"]') : null;

        function toggleAfterWorkProofLoading(isLoading) {
            if (afterWorkProofLoading) afterWorkProofLoading.classList.toggle('d-none', !isLoading);
            if (afterWorkProofSubmitBtn) afterWorkProofSubmitBtn.disabled = isLoading;
            if (afterPhotoInput) afterPhotoInput.disabled = isLoading;
        }

        if (afterPhotoInput) {
            afterPhotoInput.addEventListener('change', () => handleImageSelection(afterPhotoInput, afterPhotoPreview, afterPhotoPreviewImg));
        }

        if (uploadAfterWorkProofModalEl) {
            uploadAfterWorkProofModalEl.addEventListener('hidden.bs.modal', () => {
                toggleAfterWorkProofLoading(false);
                resetFileInput(afterPhotoInput, afterPhotoPreview, afterPhotoPreviewImg);
            });
        }

        if (uploadAfterWorkProofForm) {
            uploadAfterWorkProofForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                toggleAfterWorkProofLoading(true);
                try {
                    const processedFile = await ensureProcessedFile(afterPhotoInput);
                    if (!processedFile) {
                        if (!afterPhotoInput.files || afterPhotoInput.files.length === 0) {
                            alert('Mohon unggah foto terlebih dahulu.');
                        }
                        toggleAfterWorkProofLoading(false);
                        return;
                    }

                    const formData = new FormData();
                    formData.append('type', 'after');
                    formData.append('photo', processedFile);

                    const data = await sendFormData(`/api/service-orders/${serviceOrderId}/upload-work-proof`, formData);
                    alert(data.message || 'Foto "sesudah" berhasil diunggah. Bukti kerja lengkap.');
                    location.reload();
                } catch (error) {
                    console.error('Error:', error);
                    alert(error.message || 'Gagal mengunggah foto "sesudah".');
                    toggleAfterWorkProofLoading(false);
                }
            });
        }

    });
</script>
{{-- SO Gate JS: disable/enable proof buttons based on Mesin Pergi status --}}
<script>
(function() {
    @if($isStaff && !$hasMesinPergi)
    // Blade already rendered with disabled buttons; also enforce via JS
    function setProofButtonsDisabled(disabled) {
        var btns = ['btn-start-work', 'btn-upload-before', 'btn-upload-after'];
        btns.forEach(function(id) {
            var btn = document.getElementById(id);
            if (btn) {
                btn.disabled = disabled;
                if (disabled) {
                    btn.title = 'Upload Mesin Pergi dulu sebelum mulai kerjaan';
                } else {
                    btn.removeAttribute('title');
                }
            }
        });
    }

    // Check status on load
    fetch('/api/machine-attendance/status', { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var hasPergi = (data.status === 'active' || data.status === 'completed');
            setProofButtonsDisabled(!hasPergi);
        })
        .catch(function() {
            // On error, keep buttons as Blade rendered them (disabled)
        });
    @endif
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function() {
    const modalEl = document.getElementById('signatureModal');
    if (!modalEl) return;

    const canvas = document.getElementById('signature-pad');
    let pad;

    modalEl.addEventListener('shown.bs.modal', function () {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        if (pad) pad.clear();
        else pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
    });

    document.getElementById('clear-sig').addEventListener('click', function () {
        if (pad) pad.clear();
    });

    document.getElementById('submit-sig').addEventListener('click', async function () {
        if (!pad || pad.isEmpty()) {
            alert('Tanda tangan customer belum diisi.');
            return;
        }

        // Clear any previous error alert
        const existingErr = document.getElementById('sig-error');
        if (existingErr) existingErr.remove();

        this.disabled = true;
        this.textContent = 'Menyimpan...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const authToken = localStorage.getItem('auth_token') || '';
            const serviceOrderId = {{ $serviceOrder->id }};
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            };
            if (authToken) {
                headers['Authorization'] = 'Bearer ' + authToken;
            }

            const res = await fetch(`/api/service-orders/${serviceOrderId}/submit-signature`, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ signature_image: pad.toDataURL('image/png') })
            });
            const data = await res.json();
            if (data.success) {
                // Remove any error alert
                const existing = document.getElementById('sig-error');
                if (existing) existing.remove();

                // Close modal
                const modalInstance = bootstrap.Modal.getInstance(
                    document.getElementById('signatureModal')
                );
                if (modalInstance) modalInstance.hide();

                // Show fixed success banner at top of page
                const banner = document.createElement('div');
                banner.style.cssText = [
                    'position:fixed', 'top:16px', 'left:50%', 'transform:translateX(-50%)',
                    'z-index:99999', 'background:#2fb344', 'color:#fff', 'font-weight:600',
                    'padding:12px 28px', 'border-radius:8px', 'box-shadow:0 4px 16px rgba(0,0,0,0.2)',
                    'font-size:15px', 'text-align:center'
                ].join(';');
                banner.textContent = '✓ Tanda tangan tersimpan. Pekerjaan selesai!';
                document.body.appendChild(banner);

                // Reload after 2 seconds
                setTimeout(() => window.location.reload(), 2000);

            } else {
                // SHOW ERROR INSIDE MODAL
                let errAlert = document.getElementById('sig-error');
                if (!errAlert) {
                    errAlert = document.createElement('div');
                    errAlert.id = 'sig-error';
                    errAlert.style.cssText = 'background:#d63939;color:#fff;padding:10px 14px;border-radius:6px;margin-bottom:10px;font-size:14px;';
                    canvas.parentNode.insertBefore(errAlert, canvas);
                }
                errAlert.textContent = data.message || 'Gagal menyimpan. Coba lagi.';
                this.disabled = false;
                this.textContent = 'Simpan & Selesaikan Pekerjaan';
            }
        } catch (e) {
            let errAlert = document.getElementById('sig-error');
            if (!errAlert) {
                errAlert = document.createElement('div');
                errAlert.id = 'sig-error';
                errAlert.style.cssText = 'background:#d63939;color:#fff;padding:10px 14px;border-radius:6px;margin-bottom:10px;font-size:14px;';
                canvas.parentNode.insertBefore(errAlert, canvas);
            }
            errAlert.textContent = 'Koneksi bermasalah. Periksa internet dan coba lagi.';
            this.disabled = false;
            this.textContent = 'Simpan & Selesaikan Pekerjaan';
        }
    });
})();
</script>
{{-- Final Order JS --}}
@php
    $soDataForJs = [
        'number'   => $serviceOrder->so_number ?? $serviceOrder->id,
        'customer' => $serviceOrder->customer->name ?? '-',
        'phone'    => $serviceOrder->customer->phone_number ?? '-',
        'date'     => optional($serviceOrder->sessions->first())->tanggal?->format('d M Y') ?? '-',
        'hours'    => optional($serviceOrder->sessions->first())->jam ?? '-',
        'address'  => optional($serviceOrder->address)->full_address ?? '-',
        'items'    => $serviceOrder->items->map(fn($i) => [
            'name' => $i->service->name ?? 'Layanan',
            'qty'  => $i->quantity,
        ])->values()->toArray(),
        'staff_per_session' => $serviceOrder->sessions
            ->where('type', 'kerja')
            ->sortBy('id')
            ->values()
            ->map(fn($s, $i) => [
                'number' => $i + 1,
                'names' => $s->staff->pluck('name')->join(', ') ?: '-',
            ])->values()->toArray(),
    ];
@endphp

<script>
// ── Salin Detail Order ──────────────────────────────────────────
document.getElementById('btnSalinOrder')?.addEventListener('click', function () {
    const so = @json($soDataForJs);

    let staffLines = so.staff_per_session.map(s => `Sesi ${s.number}: ${s.names}`);
    let layanan = so.items.map(item => `- ${item.name} x${item.qty}`).join('\n');

    const text =
`Order Detail
Staff yang bertugas:
${staffLines.join('\n')}
${so.customer}
${so.address}

${layanan}`;

    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('btnSalinOrder');
        btn.textContent = '✅ Tersalin!';
        setTimeout(() => btn.innerHTML = '📋 Salin Detail Order', 2000);
    }).catch(() => alert('Gagal menyalin. Pastikan browser mengizinkan clipboard.'));
});

// ── Submit Final Order ──────────────────────────────────────────
document.getElementById('btnSubmitFinalOrder')?.addEventListener('click', function () {
    const content = document.getElementById('finalOrderContent').value.trim();
    if (!content) {
        alert('Isi final order tidak boleh kosong.');
        return;
    }

    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    fetch('{{ route('web.final-order.upsert', $serviceOrder) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ content }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan.');
            btn.disabled = false;
            btn.textContent = '💾 Simpan Final Order';
        }
    })
    .catch(() => {
        alert('Gagal menghubungi server.');
        btn.disabled = false;
        btn.textContent = '💾 Simpan Final Order';
    });
});
</script>
@endpush
