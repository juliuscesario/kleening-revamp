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
                @if($serviceOrder->status == 'booked')
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#startWorkModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 13.572l-7.5 7.428l-7.5 -7.428m0 0a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /><path d="M12 7l-2 4l2 4l2 -4l-2 -4" /></svg>
                        Mulai Kerja
                    </button>
                @elseif($serviceOrder->status == 'proses' && !$serviceOrder->work_proof_completed_at)
                    <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#uploadWorkProofModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5" /><path d="M4 15l4 -4c.928 -.893 2.072 -.893 3 0l3 3" /><path d="M14 14l1 -1c.699 -.67 1.78 -.825 2.5 -.288" /><path d="M19 22v-6" /><path d="M22 19l-3 -3l-3 3" /></svg>
                        Lengkapi Bukti Kerja
                    </button>
                @elseif($serviceOrder->status == 'proses' && $serviceOrder->work_proof_completed_at)
                    <button class="btn btn-success me-2" id="requestSignatureBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7c1.657 0 3 1.59 3 3s-1.657 3 -3 3s-3 -1.59 -3 -3s1.657 -3 3 -3" /><path d="M17 17c1.657 0 3 1.59 3 3s-1.657 3 -3 3s-3 -1.59 -3 -3s1.657 -3 3 -3" /><path d="M7 13v4a3 3 0 0 0 3 3h1" /><path d="M17 13v4a3 3 0 0 0 3 3h1" /><path d="M17 10h-1a2 2 0 0 0 -2 2v2a2 2 0 0 0 2 2h1" /><path d="M7 10h1a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-1" /></svg>
                        Minta Tanda Tangan
                    </button>
                @endif
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
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

<!-- Start Work Modal -->
<div class="modal modal-blur fade" id="startWorkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mulai Kerja untuk Service Order {{ $serviceOrder->so_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="startWorkForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photo" class="form-label">Unggah Foto Pekerjaan</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*" capture="environment" required>
                        <div class="mt-2" id="photoPreview" style="display: none;">
                            <img src="" alt="Photo Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Work Proof Modal -->
<div class="modal modal-blur fade" id="uploadWorkProofModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lengkapi Bukti Kerja untuk Service Order {{ $serviceOrder->so_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadWorkProofForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div id="beforePhotoStep">
                        <h4 class="mb-3">Foto Sebelum Pekerjaan</h4>
                        <div class="mb-3">
                            <label for="before_photo" class="form-label">Unggah Foto Sebelum</label>
                            <input type="file" class="form-control" id="before_photo" name="photo" accept="image/*" capture="environment" required>
                            <div class="mt-2" id="beforePhotoPreview" style="display: none;">
                                <img src="" alt="Before Photo Preview" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                    <div id="afterPhotoStep" style="display: none;">
                        <h4 class="mb-3">Foto Sesudah Pekerjaan</h4>
                        <div class="mb-3">
                            <label for="after_photo" class="form-label">Unggah Foto Sesudah</label>
                            <input type="file" class="form-control" id="after_photo" name="photo" accept="image/*" capture="environment" required>
                            <div class="mt-2" id="afterPhotoPreview" style="display: none;">
                                <img src="" alt="After Photo Preview" class="img-fluid rounded" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="nextWorkProofStep">Selanjutnya</button>
                    <button type="submit" class="btn btn-success" id="submitWorkProof" style="display: none;">Submit Bukti Kerja</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const startWorkForm = document.getElementById('startWorkForm');
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photoPreview');
        const photoPreviewImg = photoPreview.querySelector('img');

        photoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    photoPreviewImg.src = e.target.result;
                    photoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                photoPreview.style.display = 'none';
                photoPreviewImg.src = '';
            }
        });

        startWorkForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            const serviceOrderId = {{ $serviceOrder->id }};

            fetch(`/api/service-orders/${serviceOrderId}/start-work`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                },
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Dynamically update UI
                    document.getElementById('startWorkModal').querySelector('.btn-close').click(); // Close modal
                    document.querySelector('button[data-bs-target="#startWorkModal"]').style.display = 'none'; // Hide Mulai Kerja button
                    
                    // Show Lengkapi Bukti Kerja button if status is now proses
                    const lengkapiBuktiKerjaBtn = document.querySelector('button[data-bs-target="#uploadWorkProofModal"]');
                    if (lengkapiBuktiKerjaBtn) {
                        lengkapiBuktiKerjaBtn.style.display = 'inline-block'; // Or 'block' depending on original display
                    }

                    // Update status badge
                    const statusBadge = document.querySelector('.badge');
                    if (statusBadge) {
                        statusBadge.classList.remove('bg-primary');
                        statusBadge.classList.add('bg-warning');
                        statusBadge.textContent = 'Proses';
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while starting work.');
            });
        });

        // --- Upload Work Proof Modal Logic ---
        const uploadWorkProofModal = document.getElementById('uploadWorkProofModal');
        const uploadWorkProofForm = document.getElementById('uploadWorkProofForm');
        const beforePhotoStep = document.getElementById('beforePhotoStep');
        const afterPhotoStep = document.getElementById('afterPhotoStep');
        const beforePhotoInput = document.getElementById('before_photo');
        const afterPhotoInput = document.getElementById('after_photo');
        const beforePhotoPreview = document.getElementById('beforePhotoPreview');
        const afterPhotoPreview = document.getElementById('afterPhotoPreview');
        const beforePhotoPreviewImg = beforePhotoPreview.querySelector('img');
        const afterPhotoPreviewImg = afterPhotoPreview.querySelector('img');
        const nextWorkProofStepBtn = document.getElementById('nextWorkProofStep');
        const submitWorkProofBtn = document.getElementById('submitWorkProof');

        let currentWorkProofStep = 1; // 1 for before, 2 for after

        // Reset modal state when it's hidden
        uploadWorkProofModal.addEventListener('hidden.bs.modal', function () {
            currentWorkProofStep = 1;
            beforePhotoStep.style.display = 'block';
            afterPhotoStep.style.display = 'none';
            nextWorkProofStepBtn.style.display = 'block';
            submitWorkProofBtn.style.display = 'none';
            uploadWorkProofForm.reset();
            beforePhotoPreview.style.display = 'none';
            beforePhotoPreviewImg.src = '';
            afterPhotoPreview.style.display = 'none';
            afterPhotoPreviewImg.src = '';
        });

        beforePhotoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    beforePhotoPreviewImg.src = e.target.result;
                    beforePhotoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                beforePhotoPreview.style.display = 'none';
                beforePhotoPreviewImg.src = '';
            }
        });

        afterPhotoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    afterPhotoPreviewImg.src = e.target.result;
                    afterPhotoPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                afterPhotoPreview.style.display = 'none';
                afterPhotoPreviewImg.src = '';
            }
        });

        nextWorkProofStepBtn.addEventListener('click', function () {
            if (currentWorkProofStep === 1) {
                // Validate before photo
                if (!beforePhotoInput.files[0]) {
                    alert('Mohon unggah foto sebelum pekerjaan.');
                    return;
                }
                // Upload before photo
                uploadPhoto('before', beforePhotoInput.files[0], function () {
                    currentWorkProofStep = 2;
                    beforePhotoStep.style.display = 'none';
                    afterPhotoStep.style.display = 'block';
                    nextWorkProofStepBtn.style.display = 'none';
                    submitWorkProofBtn.style.display = 'block';
                });
            } 
        });

        submitWorkProofBtn.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default button click behavior
            if (currentWorkProofStep === 2) {
                // Validate after photo
                if (!afterPhotoInput.files[0]) {
                    alert('Mohon unggah foto sesudah pekerjaan.');
                    return;
                }
                // Upload after photo
                uploadPhoto('after', afterPhotoInput.files[0], function () {
                    alert('Bukti kerja berhasil diunggah!');
                    // Dynamically update UI
                    document.getElementById('uploadWorkProofModal').querySelector('.btn-close').click(); // Close modal
                    document.querySelector('button[data-bs-target="#uploadWorkProofModal"]').style.display = 'none'; // Hide Lengkapi Bukti Kerja button

                    // Show Minta Tanda Tangan button (assuming it exists or will be created)
                    const mintaTandaTanganBtn = document.getElementById('requestSignatureBtn'); // Assuming an ID for this button
                    if (mintaTandaTanganBtn) {
                        mintaTandaTanganBtn.style.display = 'inline-block';
                    }
                });
            }
        });

        function uploadPhoto(type, file, callback) {
            const formData = new FormData();
            formData.append('photo', file);
            formData.append('type', type);

            const serviceOrderId = {{ $serviceOrder->id }};

            fetch(`/api/service-orders/${serviceOrderId}/upload-work-proof`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                },
                body: formData,
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
                alert('An error occurred while uploading photo.');
            });
        }
    });
</script>
@endpush