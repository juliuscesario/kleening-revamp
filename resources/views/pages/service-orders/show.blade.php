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
                <div class="btn-list">
                    @if($serviceOrder->status === 'done' && (!$serviceOrder->invoice || $serviceOrder->invoice->status === 'cancelled'))
                    <a href="{{ route('web.invoices.create', ['service_order_id' => $serviceOrder->id]) }}" class="btn btn-brand shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-invoice" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M9 7l1 0" /><path d="M9 13l6 0" /><path d="M13 17l2 0" /></svg>
                        Create Invoice
                    </a>
                    @endif
                    @if(auth()->user()->role !== 'staff')
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editServiceOrderModal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                        Edit
                    </button>
                    @endif
                    <a href="{{ route('web.service-orders.print', $serviceOrder->id) }}" class="btn btn-outline-success" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><rect x="7" y="13" width="10" height="8" rx="2" /></svg>
                        Print
                    </a>
                    <a href="{{ route('web.service-orders.index') }}" class="btn btn-ghost-secondary">Kembali</a>
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Layanan yang Dipesan</h3>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#adHocMaterialModal">Catat Material Lapangan</button>
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
                                                    @php
                                                        $badgeColor = 'success';
                                                        $badgeText = ucfirst($photo->type);
                                                        if ($photo->type == 'arrival') { $badgeColor = 'primary'; }
                                                        elseif ($photo->type == 'before') { $badgeColor = 'info'; }
                                                        elseif ($photo->type == 'receipt') { $badgeColor = 'warning'; $badgeText = 'Bon Material'; }
                                                    @endphp
                                                    <span class="badge bg-{{ $badgeColor }} me-2">{{ $badgeText }}</span>
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
                        <h3 class="card-title">Material & Pengeluaran Lapangan</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Biaya Modal</th>
                                    <th>Bon/Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($serviceOrder->expenses as $expense)
                                <tr>
                                    <td>{{ $expense->name }}</td>
                                    <td>Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if($expense->photo_path)
                                            <a href="{{ Storage::url($expense->photo_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                Lihat Bon
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-sm btn-warning btn-upload-bon" data-id="{{ $expense->id }}" data-name="{{ $expense->name }}">
                                                Upload Bon
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada material yang dicatat.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
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
</div>

<!-- Modal -->
<div class="modal modal-blur fade" id="adHocMaterialModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Material Lapangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="adHocMaterialForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">Nama Material</label>
                        <input type="text" class="form-control" name="material_name" placeholder="Contoh: Pipa AC 2 Meter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Harga Beli (Modal)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="cost_price" required>
                        </div>
                        <small class="form-hint">Ini akan dicatat sebagai Pengeluaran (Expense).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="add_to_billing" id="chkAddToBilling">
                            <span class="form-check-label">Tambahkan ke tagihan customer?</span>
                        </label>
                    </div>
                    <div class="mb-3" id="divSellingPrice" style="display: none;">
                        <label class="form-label required">Harga Jual (Ke Customer)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="selling_price">
                        </div>
                        <small class="form-hint">Ini akan ditambahkan sebagai layanan pada Service Order ini.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveAdHocMaterial">Simpan Material</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Receipt Modal -->
<div class="modal modal-blur fade" id="uploadReceiptModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="uploadReceiptForm" enctype="multipart/form-data">
                <input type="hidden" name="expense_id" id="receipt_expense_id">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Upload Bon Material</h5>
                        <div class="text-muted" id="receipt_material_name"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unggah Foto Bon/Nota</label>
                        <input type="file" class="form-control" name="photo" id="receipt_photo" accept="image/*" required>
                    </div>
                     <div class="d-flex align-items-center mt-3 d-none" id="receiptWorkProofLoading">
                        <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                        <span>Sedang mengunggah foto, mohon tunggu...</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="receiptWorkProofSubmitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const chkAddToBilling = document.getElementById('chkAddToBilling');
    const divSellingPrice = document.getElementById('divSellingPrice');
    
    if (chkAddToBilling) {
        chkAddToBilling.addEventListener('change', function() {
            divSellingPrice.style.display = this.checked ? 'block' : 'none';
            const input = divSellingPrice.querySelector('input');
            if (this.checked) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });
    }

    const btnSaveAdHocMaterial = document.getElementById('btnSaveAdHocMaterial');
    if (btnSaveAdHocMaterial) {
        btnSaveAdHocMaterial.addEventListener('click', function() {
            const form = document.getElementById('adHocMaterialForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.add_to_billing = chkAddToBilling.checked ? 1 : 0;

            // Disable button and show loading state
            btnSaveAdHocMaterial.disabled = true;
            btnSaveAdHocMaterial.innerText = 'Menyimpan...';

            fetch('{{ route("web.service-orders.ad-hoc-materials", $serviceOrder->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Material berhasil ditambahkan!');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (result.message || 'Terjadi kesalahan'));
                    btnSaveAdHocMaterial.disabled = false;
                    btnSaveAdHocMaterial.innerText = 'Simpan Material';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan material.');
                btnSaveAdHocMaterial.disabled = false;
                btnSaveAdHocMaterial.innerText = 'Simpan Material';
            });
        });
    }

    // --- Upload Receipt Photo ---
    const uploadReceiptModalEl = document.getElementById('uploadReceiptModal');
    const uploadReceiptForm = document.getElementById('uploadReceiptForm');
    const receiptExpenseIdInput = document.getElementById('receipt_expense_id');
    const receiptMaterialNameDiv = document.getElementById('receipt_material_name');
    const receiptWorkProofLoading = document.getElementById('receiptWorkProofLoading');
    const receiptWorkProofSubmitBtn = document.getElementById('receiptWorkProofSubmitBtn');

    document.querySelectorAll('.btn-upload-bon').forEach(btn => {
        btn.addEventListener('click', function() {
            receiptExpenseIdInput.value = this.dataset.id;
            receiptMaterialNameDiv.innerText = this.dataset.name;
            const modal = new bootstrap.Modal(uploadReceiptModalEl);
            modal.show();
        });
    });

    if (uploadReceiptForm) {
        uploadReceiptForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const expenseId = receiptExpenseIdInput.value;
            const formData = new FormData(this);

            receiptWorkProofLoading.classList.remove('d-none');
            receiptWorkProofSubmitBtn.disabled = true;

            const uploadUrl = '{{ route("web.expenses.upload-bon", ":id") }}'.replace(':id', expenseId);

            fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (result.message || 'Terjadi kesalahan'));
                    receiptWorkProofLoading.classList.add('d-none');
                    receiptWorkProofSubmitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengunggah bon.');
                receiptWorkProofLoading.classList.add('d-none');
                receiptWorkProofSubmitBtn.disabled = false;
            });
        });
    }
});
</script>
@endpush
