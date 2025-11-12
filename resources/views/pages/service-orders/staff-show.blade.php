@extends('layouts.admin')
@section('title', 'Detail Service Order')

@section('content')
<style>
    .signature-pad-canvas {
        width: 100%;
        max-width: 500px; /* Limit max width for smaller saved images */
        height: 200px; /* Or any desired height */
        background-color: #f8f9fa; /* Light background for visibility */
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }
</style>
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
                    @if($serviceOrder->status == 'booked')
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startWorkModal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 13.572l-7.5 7.428l-7.5 -7.428m0 0a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /><path d="M12 7l-2 4l2 4l2 -4l-2 -4" /></svg>
                            Mulai Kerja
                        </button>
                    @elseif($serviceOrder->status == 'proses' && !$serviceOrder->work_proof_completed_at)
                        @php
                            $hasBeforePhoto = $serviceOrder->workPhotos()->where('type', 'before')->exists();
                        @endphp
                        @if(!$hasBeforePhoto)
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#uploadBeforeWorkProofModal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5" /><path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l3 3" /><path d="M14 14l1 -1c.699 -.67 1.78 -.825 2.5 -.288" /><path d="M19 22v-6" /><path d="M22 19l-3 -3l-3 3" /></svg>
                                Upload Foto Sebelum
                            </button>
                        @else
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#uploadAfterWorkProofModal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5" /><path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l3 3" /><path d="M14 14l1 -1c.699 -.67 1.78 -.825 2.5 -.288" /><path d="M19 22v-6" /><path d="M22 19l-3 -3l-3 3" /></svg>
                                Upload Foto Sesudah
                            </button>
                        @endif
                    @elseif($serviceOrder->status == 'proses' && $serviceOrder->work_proof_completed_at && (!$serviceOrder->customer_signature_image || $serviceOrder->staff->whereNull('pivot.signature_image')->isNotEmpty()))
                        <button class="btn btn-success" id="requestSignatureBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7c1.657 0 3 1.59 3 3s-1.657 3 -3 3s-3 -1.59 -3 -3s1.657 -3 3 -3" /><path d="M17 17c1.657 0 3 1.59 3 3s-1.657 3 -3 3s-3 -1.59 -3 -3s1.657 -3 3 -3" /><path d="M7 13v4a3 3 0 0 0 3 3h1" /><path d="M17 13v4a3 3 0 0 0 3 3h1" /><path d="M17 10h-1a2 2 0 0 0 -2 2v2a2 2 0 0 0 2 2h1" /><path d="M7 10h1a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-1" /></svg>
                            Minta Tanda Tangan
                        </button>
                    @endif
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
                </div>
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
                        <p><strong>Nomor Handphone:</strong>
                            @if ($serviceOrder->customer && $serviceOrder->customer->phone_number)
                                @php
                                    $normalizedPhone = preg_replace('/\D+/', '', $serviceOrder->customer->phone_number);
                                @endphp
                                <a href="tel:{{ $serviceOrder->customer->phone_number }}">{{ $serviceOrder->customer->phone_number }}</a>
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
<div class="modal modal-blur fade" id="signatureModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signatureModalTitle">Tanda Tangan untuk Service Order {{ $serviceOrder->so_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="customerSignatureSection">
                    <h4 class="mb-3">Tanda Tangan Pelanggan</h4>
                    <div class="mb-3">
                        <canvas id="customerSignaturePad" class="border signature-pad-canvas"></canvas>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-warning me-2" id="clearCustomerSignature">Clear</button>
                        <button type="button" class="btn btn-primary" id="saveCustomerSignature">Simpan Tanda Tangan Pelanggan</button>
                    </div>
                </div>

                <div id="staffSignatureSection" style="display: none;">
                    <h4 class="mb-3">Tanda Tangan Staff</h4>
                    <div id="staffSignaturePads"></div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-primary" id="saveStaffSignature" style="display: none;">Simpan Tanda Tangan Staff</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
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

        // --- Signature Pad Logic ---
        const signatureModalEl = document.getElementById('signatureModal');
        const signatureModal = new bootstrap.Modal(signatureModalEl);

        signatureModalEl.addEventListener('shown.bs.modal', () => {
            resizeCanvas(document.getElementById('customerSignaturePad'), customerSignaturePad);
        });
        const requestSignatureBtn = document.getElementById('requestSignatureBtn');

        const clearCustomerSignatureBtn = document.getElementById('clearCustomerSignature');
        const saveCustomerSignatureBtn = document.getElementById('saveCustomerSignature');
        const customerSignatureSection = document.getElementById('customerSignatureSection');

        const staffSignatureSection = document.getElementById('staffSignatureSection');
        const staffSignaturePadsContainer = document.getElementById('staffSignaturePads');
        const saveStaffSignatureBtn = document.getElementById('saveStaffSignature');

        let staffMembers = @json($serviceOrder->staff);
        let staffSignaturePads = {}; // To store SignaturePad instances for each staff
        let customerSigned = {{ $serviceOrder->customer_signature_image ? 'true' : 'false' }}; // Track customer signature status

        function resizeCanvas(canvas, signaturePadInstance) {
            if (canvas.offsetWidth === 0 || canvas.offsetHeight === 0) {
                return;
            }
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            if (signaturePadInstance) {
                signaturePadInstance.clear();
            }
        }

        // Function to initialize a signature pad for a given canvas ID
        function initializeSignaturePad(canvasId) {
            const canvas = document.getElementById(canvasId);
            const signaturePad = new SignaturePad(canvas);
            window.addEventListener('resize', () => resizeCanvas(canvas, signaturePad));
            return signaturePad;
        }

        const customerSignaturePad = initializeSignaturePad('customerSignaturePad');



        // Event listener for "Minta Tanda Tangan" button
        if (requestSignatureBtn) {
            requestSignatureBtn.addEventListener('click', function () {
                console.log('Minta Tanda Tangan button clicked.');
                // Reset state
                customerSignaturePad.clear();
                staffSignaturePadsContainer.innerHTML = ''; // Clear previous staff pads
                staffSignaturePads = {};
                saveStaffSignatureBtn.style.display = 'none';

                // Check if customer has already signed
                if (customerSigned) {
                    console.log('Customer already signed. Proceeding to staff signatures.');
                    customerSignatureSection.style.display = 'none';
                    staffSignatureSection.style.display = 'block';
                    renderStaffSignaturePad(); // Start with staff signatures
                } else {
                    console.log('Customer has not signed. Displaying customer signature pad.');
                    customerSignatureSection.style.display = 'block';
                    staffSignatureSection.style.display = 'none';
                }

                signatureModal.show();
            });
        }

        // Customer Signature Actions
        clearCustomerSignatureBtn.addEventListener('click', function () {
            console.log('Clear Customer Signature button clicked.');
            customerSignaturePad.clear();
        });

        saveCustomerSignatureBtn.addEventListener('click', function () {
            console.log('Save Customer Signature button clicked.');
            if (customerSignaturePad.isEmpty()) {
                alert('Mohon berikan tanda tangan pelanggan.');
                return;
            }
            const signatureData = customerSignaturePad.toDataURL();
            uploadSignature(serviceOrderId, signatureData, 'customer', null, function () {
                alert('Tanda tangan pelanggan berhasil disimpan.');
                customerSigned = true; // Update status after successful signature
                customerSignatureSection.style.display = 'none';
                staffSignatureSection.style.display = 'block';
                renderStaffSignaturePad(); // Start with staff signatures
            });
        });

        // Staff Signature Actions
        function renderStaffSignaturePad() {
            console.log('renderStaffSignaturePad called.');
            console.log('Current staffMembers state:', staffMembers);

            // Find the first staff member who hasn't signed yet
            const nextUnsignedStaff = staffMembers.find(staff => !staff.pivot.signature_image || staff.pivot.signature_image === '');
            console.log('Next unsigned staff:', nextUnsignedStaff);

            if (nextUnsignedStaff) {
                const staff = nextUnsignedStaff;
                console.log('Rendering signature pad for staff:', staff.name, '(ID:', staff.id, ')');
                staffSignaturePadsContainer.innerHTML = `
                    <h5 class="mb-2">${staff.name}</h5>
                    <canvas id="staffSignaturePad_${staff.id}" class="border signature-pad-canvas"></canvas>
                    <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-warning me-2" data-staff-id="${staff.id}" data-action="clear">Clear</button>
                        <button type="button" class="btn btn-primary" data-staff-id="${staff.id}" data-action="save">Simpan Tanda Tangan Staff</button>
                    </div>
                `;
                staffSignaturePads[staff.id] = initializeSignaturePad(`staffSignaturePad_${staff.id}`);
                resizeCanvas(document.getElementById(`staffSignaturePad_${staff.id}`), staffSignaturePads[staff.id]);

                // Add event listeners for the dynamically created buttons
                document.querySelector(`#staffSignaturePads button[data-staff-id="${staff.id}"][data-action="clear"]`).addEventListener('click', function() {
                    console.log('Clear button clicked for staff:', staff.name);
                    staffSignaturePads[staff.id].clear();
                });
                document.querySelector(`#staffSignaturePads button[data-staff-id="${staff.id}"][data-action="save"]`).addEventListener('click', function() {
                    console.log('Save button clicked for staff:', staff.name);
                    saveCurrentStaffSignature(staff.id);
                });

            } else {
                console.log('All staff have signed.');
                alert('Semua tanda tangan staff berhasil disimpan.');
                signatureModal.hide();
                // Hide the "Minta Tanda Tangan" button if all signatures are now present
                if (customerSigned && staffMembers.every(staff => staff.pivot.signature_image && staff.pivot.signature_image !== '')) {
                    console.log('All signatures complete. Hiding Minta Tanda Tangan button.');
                    requestSignatureBtn.style.display = 'none';
                    // Call function to update service order status to 'done'
                    updateServiceOrderStatus(serviceOrderId, 'done');
                }
            }
        }

        function saveCurrentStaffSignature(staffId) {
            console.log('saveCurrentStaffSignature called for staff ID:', staffId);
            const signaturePad = staffSignaturePads[staffId];
            if (signaturePad.isEmpty()) {
                alert('Mohon berikan tanda tangan untuk staff ini.');
                return;
            }
            const signatureData = signaturePad.toDataURL();
            uploadSignature(serviceOrderId, signatureData, 'staff', staffId, function () {
                alert('Tanda tangan staff berhasil disimpan.');
                // Update the specific staff member's signature_image in the local staffMembers array
                const signedStaffIndex = staffMembers.findIndex(staff => staff.id == staffId);
                if (signedStaffIndex !== -1) {
                    staffMembers[signedStaffIndex].pivot.signature_image = signatureData; // Store the actual signature or a placeholder
                    console.log('Updated staffMembers after staff signature:', staffMembers[signedStaffIndex]);
                }
                // After saving, re-render to find the next unsigned staff
                renderStaffSignaturePad();
            });
        }

        // Generic upload signature function
        function uploadSignature(serviceOrderId, signatureData, signerType, staffId, callback) {
            const payload = {
                signature_image: signatureData,
                signer_type: signerType,
            };
            if (signerType === 'staff') {
                payload.staff_id = staffId;
            }

            fetch(`/api/service-orders/${serviceOrderId}/signature`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                },
                body: JSON.stringify(payload),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    callback();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading signature.');
            });
        }

        // Function to update service order status
        function updateServiceOrderStatus(serviceOrderId, newStatus) {
            fetch(`/api/service-orders/${serviceOrderId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                },
                body: JSON.stringify({ status: newStatus }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Service Order status updated to ' + newStatus + ' successfully!');
                    // Optionally, update the status badge on the page without a full reload
                    const statusBadge = document.querySelector('.badge');
                    if (statusBadge) {
                        statusBadge.classList.remove('bg-warning', 'bg-primary', 'bg-danger', 'bg-secondary'); // Remove old status classes
                        statusBadge.classList.add('bg-success'); // Add new status class
                        statusBadge.textContent = 'Done'; // Update text
                    }
                } else {
                    alert('Error updating Service Order status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the Service Order status.');
            });
        }

    });
</script>
@endpush
