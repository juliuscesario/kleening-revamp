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

                    <!-- Address Selection (shown only for existing customer with multiple addresses) -->
                    <div id="parserAddressSection" class="mt-3" style="display: none;">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h4 class="card-title">Pilih Alamat Customer</h4>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success mb-3">
                                    ✅ Customer ditemukan: <strong id="parserCustomerName"></strong>
                                    <span class="text-muted ms-2" id="parserCustomerPhone"></span>
                                </div>
                                <select id="parserAddressSelect" class="form-select mb-3"></select>
                                <button id="btnPrefillSO" class="btn btn-success">Isi Form SO</button>
                            </div>
                        </div>
                    </div>

                    <!-- New Customer Notice (shown when customer not found) -->
                    <div id="newCustomerNotice" class="mt-3" style="display: none;">
                        <div class="alert alert-warning d-flex align-items-center">
                            <span>⚠️ Customer baru — belum ada di sistem.</span>
                        </div>
                        <button id="btnCreateNewCustomer" class="btn btn-warning">
                            + Buat Customer Baru
                        </button>
                        <small class="text-muted ms-2">(Segment 3 — coming soon)</small>
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
// ============================================
// SEGMENT 2: Parse + Auto Check Customer
// ============================================

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
    document.getElementById('parserAddressSection').style.display = 'none';
    document.getElementById('newCustomerNotice').style.display = 'none';

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

            // Build and show preview card
            let previewHtml = buildPreviewHtml(data, result.customer_found);
            document.getElementById('parsedResultBody').innerHTML = previewHtml;
            resultCard.style.display = 'block';

            // Update textarea with cleaned text
            document.getElementById('rawFormOrder').value = buildCleanedText(data);

            // Store parsed data globally
            window.parsedFormOrder = data;

            if (result.customer_found) {
                // === EXISTING CUSTOMER ===
                window.foundCustomer = result.customer;
                const addressCount = result.addresses ? result.addresses.length : 0;

                if (addressCount === 1) {
                    // Exactly 1 address — auto-prefill immediately
                    prefillSOForm(data, result.customer, result.addresses[0].id);

                } else if (addressCount > 1) {
                    // Multiple addresses — show picker
                    document.getElementById('parserCustomerName').textContent = result.customer.name;
                    document.getElementById('parserCustomerPhone').textContent = '(' + result.customer.phone + ')';

                    const addressSelect = document.getElementById('parserAddressSelect');
                    addressSelect.innerHTML = '';
                    result.addresses.forEach(function(addr) {
                        const option = document.createElement('option');
                        option.value = addr.id;
                        option.textContent = addr.label;
                        addressSelect.appendChild(option);
                    });
                    document.getElementById('parserAddressSection').style.display = 'block';

                } else {
                    // 0 addresses — prefill without address
                    prefillSOForm(data, result.customer, null);
                }

            } else {
                // === NEW CUSTOMER ===
                document.getElementById('newCustomerNotice').style.display = 'block';
            }
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

// --- Isi Form SO (for multiple addresses case) ---
document.getElementById('btnPrefillSO').addEventListener('click', function() {
    const parsedData = window.parsedFormOrder;
    const customer = window.foundCustomer;

    if (!parsedData || !customer) {
        Swal.fire('Error', 'Data tidak lengkap', 'warning');
        return;
    }

    const selectedAddressId = document.getElementById('parserAddressSelect').value;
    if (!selectedAddressId) {
        Swal.fire('Peringatan', 'Pilih alamat terlebih dahulu', 'warning');
        return;
    }

    prefillSOForm(parsedData, customer, selectedAddressId);
    document.getElementById('parserAddressSection').style.display = 'none';
});

// --- Buat Customer Baru (Segment 3 placeholder) ---
document.getElementById('btnCreateNewCustomer').addEventListener('click', function() {
    Swal.fire('Info', 'Fitur Buat Customer Baru akan dibuat di Segment 3', 'info');
});

/**
 * Reusable prefill function.
 * Sets customer, address (if provided), work date/time, and notes in the SO form.
 */
function prefillSOForm(parsedData, customer, addressId) {
    const customerSearchInput = document.getElementById('customer-search');
    const customerIdInput = document.getElementById('customer_id');

    if (customerSearchInput && customerIdInput) {
        customerSearchInput.value = customer.name;
        customerIdInput.value = customer.id;

        const form = document.getElementById('create-so-form');
        const addressesUrlTemplate = form.dataset.addressesUrlTemplate;
        const url = addressesUrlTemplate.replace('__CUSTOMER_ID__', customer.id);

        const addressSelect = document.getElementById('address-select');
        if (addressSelect) {
            addressSelect.innerHTML = '<option value="">Loading...</option>';
            addressSelect.disabled = true;

            fetch(url)
                .then(function(res) { return res.json(); })
                .then(function(addresses) {
                    addressSelect.innerHTML = '<option value="">Pilih Alamat</option>';
                    addresses.forEach(function(addr) {
                        const option = new Option(addr.full_address, addr.id);
                        option.dataset.areaName = addr.area.name;
                        option.dataset.areaId = addr.area.id;
                        option.dataset.lokasi = addr.lokasi || '';
                        addressSelect.add(option);
                    });
                    addressSelect.disabled = false;

                    if (addressId) {
                        addressSelect.value = addressId;
                        addressSelect.dispatchEvent(new Event('change'));
                    }
                })
                .catch(function(err) {
                    console.error('Failed to load addresses:', err);
                });
        }
    }

    const workDateInput = document.querySelector('[name="work_date"]');
    if (workDateInput && parsedData.tanggal_kerja_raw) {
        workDateInput.value = parsedData.tanggal_kerja_raw;
        workDateInput.dispatchEvent(new Event('change'));
    }

    const workTimeInput = document.querySelector('[name="work_time"]');
    if (workTimeInput && parsedData.jam) {
        workTimeInput.value = parsedData.jam;
        workTimeInput.dispatchEvent(new Event('change'));
    }

    const notesInput = document.querySelector('[name="staff_notes"]');
    if (notesInput && parsedData.notes) {
        notesInput.value = parsedData.notes;
    }
}

function buildPreviewHtml(data, customerFound) {
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

    // For existing customers: show raw address. For new: show geocoded with badge.
    const addressBadge = (!customerFound && data.geocoding_success)
        ? ' <span class="badge bg-green-lt">✓ Google Maps</span>'
        : (customerFound ? ' <span class="badge bg-blue-lt">Alamat Tersimpan</span>' : ' <span class="badge bg-yellow-lt">Manual</span>');

    html += `<div class="datagrid-item">
        <div class="datagrid-title">Alamat</div>
        <div class="datagrid-content">${escapeHtml(data.alamat || '-')}${addressBadge}</div>
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
