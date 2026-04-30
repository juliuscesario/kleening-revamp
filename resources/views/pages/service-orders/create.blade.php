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

                    <div class="d-flex gap-2">
                        <button id="btnProses" class="btn btn-primary">
                            Proses
                        </button>
                        <button id="btnResetParser" class="btn btn-outline-secondary">
                            Reset
                        </button>
                    </div>

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
                                <div id="openSOSAlert" class="alert alert-warning mb-3" style="display: none;"></div>
                                <select id="parserAddressSelect" class="form-select mb-3"></select>
                                <button id="btnPrefillSO" class="btn btn-success">Isi Form SO</button>
                            </div>
                        </div>
                    </div>

                    <!-- Open SOs Warning (shown when existing customer has active SOs, single/0 address case) -->
                    <div id="openSOSAlertStandalone" class="mt-3" style="display: none;">
                        <div class="alert alert-warning d-flex align-items-start">
                            <span id="openSOSAlertStandaloneBody"></span>
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

<!-- New Customer Modal (Parser Flow) -->
<div class="modal modal-blur fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Customer Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Customer Section -->
                <h4 class="mb-3">Data Customer</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Customer</label>
                        <input type="text" class="form-control" id="newCustName">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">No. Telepon</label>
                        <input type="text" class="form-control" id="newCustPhone">
                    </div>
                </div>

                <hr>

                <!-- Address Section -->
                <h4 class="mb-3">Alamat</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Label Alamat</label>
                        <input type="text" class="form-control" id="newCustLabel" value="Rumah">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Area</label>
                        <select class="form-select" id="newCustArea">
                            <option value="">Pilih Area</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Lokasi</label>
                        <input type="text" class="form-control" id="newCustLokasi" maxlength="100" placeholder="e.g. Ciputat, Kosambi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Google Maps</label>
                        <input type="text" class="form-control" id="newCustGoogleMaps" placeholder="https://...">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Alamat Lengkap</label>
                    <textarea class="form-control" id="newCustAddress" rows="3"></textarea>
                </div>

                <hr>

                <!-- Contact Person Section -->
                <h4 class="mb-3">Kontak di Lokasi</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Kontak</label>
                        <input type="text" class="form-control" id="newCustContactName">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">No. Telp Kontak</label>
                        <input type="text" class="form-control" id="newCustContactPhone">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveNewCustomer">
                    Simpan & Lanjut
                </button>
            </div>
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
                const openSOs = result.open_sos || [];

                // Show open SOs warning if any
                renderOpenSOsWarning(openSOs);

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

/**
 * Render open SOs warning alert.
 * Stores openSOs in window._lastOpenSOs for reuse after address picker is dismissed.
 */
function renderOpenSOsWarning(openSOs) {
    // Hide both containers first
    document.getElementById('openSOSAlert').style.display = 'none';
    document.getElementById('openSOSAlertStandalone').style.display = 'none';

    window._lastOpenSOs = openSOs;

    if (!openSOs || openSOs.length === 0) return;

    const count = openSOs.length;
    const soList = openSOs.map(function(so) {
        const services = (so.services || []).join(', ');
        const servicePart = services ? ' — ' + escapeHtml(services) : '';
        return '<strong>#' + escapeHtml(so.so_number) + '</strong> (' + escapeHtml(so.status) + ', ' + escapeHtml(so.work_date || '-') + ')' + servicePart;
    }).join(', ');

    const message = 'Pelanggan ini memiliki <strong>' + count + ' SO aktif</strong>: ' + soList;

    // If address section is visible, use the inline alert
    if (document.getElementById('parserAddressSection').style.display !== 'none') {
        const alertEl = document.getElementById('openSOSAlert');
        alertEl.innerHTML = '<span>⚠️ ' + message + '</span>';
        alertEl.style.display = 'block';
    } else {
        // Use standalone alert
        const alertEl = document.getElementById('openSOSAlertStandalone');
        document.getElementById('openSOSAlertStandaloneBody').innerHTML = '<span>⚠️ ' + message + '</span>';
        alertEl.style.display = 'block';
    }
}

// --- Reset Parser UI ---
document.getElementById('btnResetParser').addEventListener('click', function() {
    resetParserUI();
});

function resetParserUI() {
    // Clear textarea
    document.getElementById('rawFormOrder').value = '';

    // Hide all parser outputs
    document.getElementById('parsedResultCard').style.display = 'none';
    document.getElementById('parsedResultBody').innerHTML = '';
    document.getElementById('parserAddressSection').style.display = 'none';
    document.getElementById('openSOSAlert').style.display = 'none';
    document.getElementById('openSOSAlertStandalone').style.display = 'none';
    document.getElementById('newCustomerNotice').style.display = 'none';

    // Reset any prefilled SO form fields
    const customerSearchInput = document.getElementById('customer-search');
    const customerIdInput = document.getElementById('customer_id');
    if (customerSearchInput) customerSearchInput.value = '';
    if (customerIdInput) customerIdInput.value = '';

    const addressSelect = document.getElementById('address-select');
    if (addressSelect) {
        addressSelect.innerHTML = '<option value="">Pilih Customer terlebih dahulu</option>';
        addressSelect.disabled = true;
    }

    const areaNameInput = document.getElementById('area-name');
    const areaIdInput = document.getElementById('area-id');
    if (areaNameInput) areaNameInput.value = '';
    if (areaIdInput) areaIdInput.value = '';

    const lokasiInput = document.getElementById('lokasi');
    if (lokasiInput) lokasiInput.value = '';

    // Reset work date/time to defaults
    const workDateInput = document.querySelector('[name="work_date"]');
    if (workDateInput) workDateInput.value = "{{ date('Y-m-d') }}";

    const workTimeInput = document.querySelector('[name="work_time"]');
    if (workTimeInput) workTimeInput.value = "{{ now()->setTimezone('Asia/Jakarta')->format('H:i') }}";

    // Reset notes
    const workNotesInput = document.querySelector('[name="work_notes"]');
    if (workNotesInput) workNotesInput.value = '';
    const staffNotesInput = document.querySelector('[name="staff_notes"]');
    if (staffNotesInput) staffNotesInput.value = '';

    // Clear service items
    const serviceContainer = document.getElementById('service-items-container');
    if (serviceContainer) serviceContainer.innerHTML = '';

    // Clear staff
    const staffContainer = document.getElementById('staff-container');
    if (staffContainer) staffContainer.innerHTML = '';

    // Reset global vars
    window.parsedFormOrder = null;
    window.foundCustomer = null;
    window._lastOpenSOs = null;

    // Disable add staff button
    const addStaffBtn = document.getElementById('add-staff-member');
    if (addStaffBtn) {
        addStaffBtn.disabled = true;
        addStaffBtn.textContent = 'Pilih Alamat terlebih dahulu';
    }
}

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

    // Re-show open SOs warning as standalone after hiding address section
    if (window._lastOpenSOs && window._lastOpenSOs.length > 0) {
        renderOpenSOsWarning(window._lastOpenSOs);
    }
});

// --- Buat Customer Baru (Segment 3) ---
let areasLoaded = false;

document.getElementById('btnCreateNewCustomer').addEventListener('click', function() {
    const data = window.parsedFormOrder;
    if (!data) return;

    // Prefill from parsed data
    document.getElementById('newCustName').value = data.nama || '';
    document.getElementById('newCustPhone').value = data.no_hp || '';
    document.getElementById('newCustAddress').value = data.alamat || '';
    document.getElementById('newCustGoogleMaps').value = data.google_maps || '';
    document.getElementById('newCustLokasi').value = data.lokasi || '';
    document.getElementById('newCustLabel').value = 'Rumah';

    // Contact person = same as customer by default
    document.getElementById('newCustContactName').value = data.nama || '';
    document.getElementById('newCustContactPhone').value = data.no_hp || '';

    // Reset area selection
    document.getElementById('newCustArea').value = '';

    // Auto-select area based on kota from geocoding
    if (!areasLoaded) {
        loadAreasForModal().then(function() {
            autoSelectArea(data.kota);
        });
    } else {
        autoSelectArea(data.kota);
    }

    // Open modal
    const modal = new bootstrap.Modal(document.getElementById('newCustomerModal'));
    modal.show();
});

// Load areas via AJAX into the modal select
async function loadAreasForModal() {
    return new Promise(function(resolve) {
        fetch('/data/areas')
            .then(function(res) { return res.json(); })
            .then(function(result) {
                const areas = Array.isArray(result.data) ? result.data : (Array.isArray(result) ? result : []);

                const select = document.getElementById('newCustArea');
                areas.forEach(function(area) {
                    const option = document.createElement('option');
                    option.value = area.id;
                    option.textContent = area.name;
                    select.appendChild(option);
                });
                areasLoaded = true;
                resolve();
            })
            .catch(function(err) {
                console.error('Failed to load areas:', err);
                resolve();
            });
    });
}

// Auto-select area based on kota from geocoding
function autoSelectArea(kota) {
    if (!kota) return;

    const kotaLower = kota.toLowerCase();
    const areaSelect = document.getElementById('newCustArea');

    let targetArea = null;

    // Determine which area based on kota
    if (kotaLower.includes('malang')) {
        targetArea = 'malang';
    } else if (kotaLower.includes('serang')) {
        targetArea = 'serang';
    } else if (
        kotaLower.includes('jakarta') ||
        kotaLower.includes('bogor') ||
        kotaLower.includes('tangerang') ||
        kotaLower.includes('depok') ||
        kotaLower.includes('bekasi')
    ) {
        targetArea = 'jadetabek';
    } else {
        // Default to jadetabek for any other area
        targetArea = 'jadetabek';
    }

    // Find and select the matching option
    Array.from(areaSelect.options).forEach(function(option) {
        if (option.textContent.toLowerCase().includes(targetArea)) {
            areaSelect.value = option.value;
        }
    });
}

// One-way sync: when customer name changes, update contact name (unless manually edited)
(function() {
    let contactNameEdited = false;
    let contactPhoneEdited = false;

    const contactNameInput = document.getElementById('newCustContactName');
    const contactPhoneInput = document.getElementById('newCustContactPhone');
    const nameInput = document.getElementById('newCustName');
    const phoneInput = document.getElementById('newCustPhone');
    const modal = document.getElementById('newCustomerModal');

    if (!contactNameInput || !contactPhoneInput || !nameInput || !phoneInput) return;

    contactNameInput.addEventListener('input', function() { contactNameEdited = true; });
    contactPhoneInput.addEventListener('input', function() { contactPhoneEdited = true; });

    nameInput.addEventListener('input', function() {
        if (!contactNameEdited) contactNameInput.value = this.value;
    });
    phoneInput.addEventListener('input', function() {
        if (!contactPhoneEdited) contactPhoneInput.value = this.value;
    });

    // Reset flags when modal opens
    modal.addEventListener('show.bs.modal', function() {
        contactNameEdited = false;
        contactPhoneEdited = false;
    });
})();

// Save new customer
document.getElementById('btnSaveNewCustomer').addEventListener('click', async function() {
    const btn = this;

    const payload = {
        name: document.getElementById('newCustName').value.trim(),
        phone: document.getElementById('newCustPhone').value.trim(),
        address: {
            label: document.getElementById('newCustLabel').value.trim(),
            area_id: document.getElementById('newCustArea').value,
            lokasi: document.getElementById('newCustLokasi').value.trim(),
            full_address: document.getElementById('newCustAddress').value.trim(),
            google_maps_link: document.getElementById('newCustGoogleMaps').value.trim(),
            contact_name: document.getElementById('newCustContactName').value.trim(),
            contact_phone: document.getElementById('newCustContactPhone').value.trim(),
        }
    };

    // Client-side validation
    if (!payload.name) { Swal.fire('Error', 'Nama customer harus diisi', 'warning'); return; }
    if (!payload.phone) { Swal.fire('Error', 'No telepon harus diisi', 'warning'); return; }
    if (!payload.address.area_id) { Swal.fire('Error', 'Area harus dipilih', 'warning'); return; }
    if (!payload.address.lokasi) { Swal.fire('Error', 'Lokasi harus diisi', 'warning'); return; }
    if (!payload.address.full_address) { Swal.fire('Error', 'Alamat lengkap harus diisi', 'warning'); return; }
    if (!payload.address.contact_name) { Swal.fire('Error', 'Nama kontak harus diisi', 'warning'); return; }
    if (!payload.address.contact_phone) { Swal.fire('Error', 'No telp kontak harus diisi', 'warning'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

    try {
        const response = await fetch('/form-order/create-customer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (!response.ok) {
            if (result.errors) {
                const errorMessages = Object.values(result.errors).flat().join('\n');
                Swal.fire('Validation Error', errorMessages, 'error');
            } else {
                Swal.fire('Error', result.message || 'Gagal menyimpan customer', 'error');
            }
            return;
        }

        if (result.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();

            // Store the new customer data
            window.foundCustomer = result.customer;

            // Auto-prefill SO form with the new customer + their new address
            const parsedData = window.parsedFormOrder;
            prefillSOForm(parsedData, result.customer, result.address_id);

            // Update the new customer notice to show success
            document.getElementById('newCustomerNotice').innerHTML =
                '<div class="alert alert-success">✅ Customer baru dibuat: <strong>' +
                escapeHtml(result.customer.name) + '</strong> — Form SO sudah diisi otomatis</div>';

            // Show success toast
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Customer baru berhasil dibuat',
                text: 'Form SO sudah diisi otomatis',
                showConfirmButton: false,
                timer: 3000,
            });
        }
    } catch (error) {
        console.error('Create customer error:', error);
        Swal.fire('Error', 'Terjadi kesalahan saat menyimpan', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Simpan & Lanjut';
    }
});

/**
 * Reusable prefill function.
 * Sets customer, address (if provided), work date/time, and notes in the SO form.
 * Handles both existing and brand-new customers.
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

                    if (addresses && addresses.length > 0) {
                        addresses.forEach(function(addr) {
                            const option = new Option(addr.full_address, addr.id);
                            option.dataset.areaName = addr.area.name;
                            option.dataset.areaId = addr.area.id;
                            option.dataset.lokasi = addr.lokasi || '';
                            addressSelect.add(option);
                        });
                    }

                    // If we have a specific addressId and it wasn't loaded via AJAX
                    // (e.g. brand new customer, or caching), manually inject it
                    if (addressId) {
                        if (!addressSelect.querySelector('option[value="' + addressId + '"]')) {
                            // Address not in the fetched list — create option manually
                            const opt = document.createElement('option');
                            opt.value = addressId;
                            opt.textContent = parsedData.alamat || 'Alamat Baru';
                            opt.selected = true;
                            opt.dataset.areaName = '';
                            opt.dataset.areaId = '';
                            opt.dataset.lokasi = parsedData.lokasi || '';
                            addressSelect.add(opt);
                        } else {
                            addressSelect.value = addressId;
                        }
                    }

                    addressSelect.disabled = false;

                    // Trigger change to load staff for the area
                    addressSelect.dispatchEvent(new Event('change'));
                })
                .catch(function(err) {
                    console.error('Failed to load addresses:', err);
                    // Fallback: still set the address if we have it
                    if (addressId) {
                        addressSelect.innerHTML = '<option value="' + addressId + '" selected>' + (parsedData.alamat || 'Alamat Baru') + '</option>';
                        addressSelect.disabled = false;
                        addressSelect.dispatchEvent(new Event('change'));
                    }
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
