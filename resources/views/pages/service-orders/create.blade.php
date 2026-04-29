@extends('layouts.admin')
@section('title', 'Buat Service Order Baru')

@push('styles')
<style>
    #parsedResultCard .datagrid {
        --tblr-datagrid-item-width: 200px;
    }
    #rawFormOrder {
        font-family: monospace;
        font-size: 13px;
    }
    .custom-search-wrapper {
        position: relative;
    }
    .custom-search-results {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 .25rem .25rem;
        max-height: 250px;
        overflow-y: auto;
        background-color: #fff;
        z-index: 1050;
    }
    .custom-search-results.is-active {
        display: block;
    }
    .result-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
    }
    .result-item:last-child {
        border-bottom: none;
    }
    .result-item:hover {
        background-color: #f8f9fa;
    }
    .result-item.is-highlighted {
        background-color: #0d6efd;
        color: #fff;
    }
    [data-bs-theme="dark"] .custom-search-results {
        background-color: var(--bg-surface, #121212);
        border-color: var(--border-color, #2a2a2a);
    }
    [data-bs-theme="dark"] .result-item {
        border-bottom-color: var(--border-color, #2a2a2a);
        color: var(--text-main, #FAF9F6);
    }
    [data-bs-theme="dark"] .result-item:hover {
        background-color: var(--bg-canvas, #0a0a0a);
    }
    [data-bs-theme="dark"] .result-item.is-highlighted {
        background-color: #0d6efd;
        color: #fff;
    }
</style>
@endpush

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Buat Service Order Baru</h2>
            </div>
        </div>
    </div>

    <div class="page-body">
        <!-- Form Order Parser Panel -->
        <div class="card mb-3">
            <div class="card-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#formOrderParser">
                <h3 class="card-title d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                        <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                    </svg>
                    Form Order Parser
                </h3>
                <span class="text-muted ms-2">Paste dari WhatsApp</span>
            </div>
            <div id="formOrderParser" class="collapse show">
                <div class="card-body">
                    <div class="mb-3">
                        <textarea id="rawFormOrder" class="form-control" rows="10"
                            placeholder="Paste form order dari WhatsApp di sini..."></textarea>
                    </div>

                    <button id="btnProses" class="btn btn-primary">
                        Proses
                    </button>

                    <div id="parseLoading" class="mt-3" style="display: none;">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="ms-2 text-muted">Memproses form order...</span>
                    </div>

                    <div id="parsedResultCard" class="mt-3" style="display: none;">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h4 class="card-title">Hasil Parse</h4>
                            </div>
                            <div class="card-body p-3" id="parsedResultBody">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <form id="create-so-form" 
                method="POST" 
                action="{{ route('web.service-orders.store') }}"
                data-customers-url="{{ route('data.customers') }}"
                data-addresses-url-template="{{ route('data.customers.addresses', ['customer' => '__CUSTOMER_ID__']) }}"
                data-services-url="{{ route('data.services') }}"
                data-staff-by-area-url-template="{{ route('data.staff.by-area', ['area' => '__AREA_ID__']) }}"
                data-pending-check-url-template="{{ route('data.customers.pending-service-orders', ['customer' => '__CUSTOMER_ID__']) }}"
            >
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Customer</label>
                            <div class="custom-search-wrapper">
                                <input type="text" id="customer-search" class="form-control" placeholder="Cari nama customer..." autocomplete="off">
                                <input type="hidden" name="customer_id" id="customer_id" required>
                                <div id="customer-results" class="custom-search-results"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alamat</label>
                            <select id="address-select" name="address_id" class="form-select" required disabled>
                                <option value="">Pilih Customer terlebih dahulu</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area</label>
                            <input type="text" id="area-name" class="form-control" readonly>
                            <input type="hidden" id="area-id" name="area_id">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi</label>
                            <input type="text" id="lokasi" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Pengerjaan</label>
                            <input type="date" name="work_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Pengerjaan (WIB)</label>
                            <input type="text"
                                name="work_time"
                                class="form-control js-work-time-input"
                                inputmode="numeric"
                                pattern="^([01]\d|2[0-3]):[0-5]\d$"
                                placeholder="00:00"
                                required
                                value="{{ old('work_time', now()->setTimezone('Asia/Jakarta')->format('H:i')) }}">
                            <small class="form-text text-muted">Masukkan 24 jam (00:00 - 23:59), contoh: 07:30 atau 16:45.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Invoicing Notes</label>
                            <textarea name="work_notes" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Notes Internal</label>
                            <textarea name="staff_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ordered Services</label>
                        <div id="service-items-container">
                            {{-- Items will be added here dynamically --}}
                        </div>
                        <button type="button" class="btn btn-success mt-2 w-100" id="add-service-item">Add Service</button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assigned Staff</label>
                        <div id="staff-container">
                            {{-- Staff will be added here dynamically --}}
                        </div>
                        <button type="button" class="btn btn-success mt-2 w-100" id="add-staff-member" disabled>Pilih Alamat terlebih dahulu</button>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Buat Service Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('btnProses').addEventListener('click', async function() {
    const rawText = document.getElementById('rawFormOrder').value.trim();
    if (!rawText) {
        Swal.fire('Error', 'Paste form order terlebih dahulu', 'warning');
        return;
    }

    const btn = this;
    const loading = document.getElementById('parseLoading');
    const resultCard = document.getElementById('parsedResultCard');

    btn.disabled = true;
    loading.style.display = 'block';
    resultCard.style.display = 'none';

    try {
        const response = await fetch('/form-order/parse', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ raw_text: rawText }),
        });

        const result = await response.json();

        if (result.success) {
            const data = result.data;

            let previewHtml = buildPreviewHtml(data);
            document.getElementById('parsedResultBody').innerHTML = previewHtml;
            resultCard.style.display = 'block';

            document.getElementById('rawFormOrder').value = buildCleanedText(data);

            window.parsedFormOrder = data;
        } else {
            Swal.fire('Error', result.message || 'Gagal memproses form order', 'error');
        }
    } catch (error) {
        console.error('Parse error:', error);
        Swal.fire('Error', 'Terjadi kesalahan saat memproses', 'error');
    } finally {
        btn.disabled = false;
        loading.style.display = 'none';
    }
});

function buildPreviewHtml(data) {
    let html = '<div class="datagrid">';

    html += `<div class="datagrid-item">
        <div class="datagrid-title">Tanggal Kerja</div>
        <div class="datagrid-content">${escapeHtml(data.tanggal_kerja || '-')}</div>
    </div>`;

    html += `<div class="datagrid-item">
        <div class="datagrid-title">Jam</div>
        <div class="datagrid-content">${escapeHtml(data.jam || '-')}</div>
    </div>`;

    html += `<div class="datagrid-item">
        <div class="datagrid-title">Nama</div>
        <div class="datagrid-content">${escapeHtml(data.nama || '-')}</div>
    </div>`;

    html += `<div class="datagrid-item">
        <div class="datagrid-title">No HP</div>
        <div class="datagrid-content">${escapeHtml(data.no_hp || '-')}</div>
    </div>`;

    html += `<div class="datagrid-item">
        <div class="datagrid-title">Alamat</div>
        <div class="datagrid-content">${escapeHtml(data.alamat || '-')}${data.geocoding_success ? ' <span class="badge bg-green-lt">✓ Google Maps</span>' : ' <span class="badge bg-yellow-lt">Manual</span>'}</div>
    </div>`;

    html += `<div class="datagrid-item">
        <div class="datagrid-title">Google Maps</div>
        <div class="datagrid-content">${data.google_maps ? '<a href="' + escapeHtml(data.google_maps) + '" target="_blank" class="text-truncate d-inline-block" style="max-width: 300px;">' + escapeHtml(data.google_maps) + '</a>' : '-'}</div>
    </div>`;

    html += `<div class="datagrid-item" style="grid-column: span 2;">
        <div class="datagrid-title">Services (raw — manual entry)</div>
        <div class="datagrid-content"><pre class="mb-0" style="white-space: pre-wrap; font-size: inherit; font-family: inherit;">${escapeHtml(data.services_raw || '-')}</pre></div>
    </div>`;

    if (data.notes) {
        html += `<div class="datagrid-item" style="grid-column: span 2;">
            <div class="datagrid-title">Notes</div>
            <div class="datagrid-content">${escapeHtml(data.notes)}</div>
        </div>`;
    }

    html += '</div>';
    return html;
}

function buildCleanedText(data) {
    let lines = [];
    lines.push('FORM ORDER KLEENING ID');
    lines.push('Tanggal Kerja : ' + (data.tanggal_kerja || ''));
    lines.push('Jam Kedatangan : ' + (data.jam || ''));
    lines.push('Nama : ' + (data.nama || ''));
    lines.push('No HP : ' + (data.no_hp || ''));
    lines.push('');
    lines.push('Alamat Lengkap: ' + (data.alamat || ''));
    lines.push('Google maps: ' + (data.google_maps || ''));
    lines.push('Service:');
    if (data.services_raw) {
        lines.push(data.services_raw);
    }
    if (data.notes) {
        lines.push('');
        lines.push('Notes: ' + data.notes);
    }
    return lines.join('\n');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
