// This script is loaded via app.js when a form with id 'create-so-form' is present.
const form = document.getElementById('create-so-form');

// Guard clause in case the script is somehow loaded on the wrong page.
if (form) {
    // --- DOM Elements ---
    const customerSearchInput = document.getElementById('customer-search');
    const customerIdInput = document.getElementById('customer_id');
    const customerResultsContainer = document.getElementById('customer-results');
    
    const addressSelect = document.getElementById('address-select');
    const areaNameInput = document.getElementById('area-name');
    const areaIdInput = document.getElementById('area-id');
    
    const addServiceBtn = document.getElementById('add-service-item');
    const serviceItemsContainer = document.getElementById('service-items-container');
    
    const addStaffBtn = document.getElementById('add-staff-member');
    const staffContainer = document.getElementById('staff-container');

    // --- URLs ---
    const customersUrl = form.dataset.customersUrl;
    const servicesUrl = form.dataset.servicesUrl;
    const addressesUrlTemplate = form.dataset.addressesUrlTemplate;
    const staffByAreaUrlTemplate = form.dataset.staffByAreaUrlTemplate;
    const pendingCheckUrlTemplate = form.dataset.pendingCheckUrlTemplate;

    // --- State ---
    let allServices = [];
    let availableStaff = [];
    let serviceItemCount = 0;
    let staffMemberCount = 0;
    let debounceTimer;
    let isSubmitting = false;

    const showWarning = (messageOrOptions) => {
        const options = typeof messageOrOptions === 'string'
            ? { text: messageOrOptions }
            : (messageOrOptions || {});

        const {
            title = 'Peringatan',
            text = '',
            html = '',
        } = options;

        if (window.Swal && typeof window.Swal.fire === 'function') {
            Swal.fire({
                icon: 'warning',
                title,
                ...(html ? { html } : { text: text || 'Terjadi peringatan.' }),
            });
        } else {
            const fallbackMessage = text || (html ? html.replace(/<[^>]+>/g, '') : 'Terjadi peringatan.');
            alert(fallbackMessage);
        }
    };

    const buildBlockingHtml = (status) => {
        if (!status) {
            return '';
        }

        const sections = [];

        if (status.has_pending && Array.isArray(status.orders) && status.orders.length > 0) {
            const items = status.orders.map((order) => `<li><strong>${order.so_number}</strong> &mdash; status: ${order.status}</li>`).join('');
            sections.push(`<p>Customer masih memiliki Service Order aktif:</p><ul>${items}</ul>`);
        }

        if (status.has_overdue_invoices && Array.isArray(status.overdue_invoices) && status.overdue_invoices.length > 0) {
            const invoices = status.overdue_invoices.map((invoice) => {
                const overdueDays = typeof invoice.overdue_days === 'number' ? `${invoice.overdue_days} hari` : '-';
                const dueDateLabel = invoice.due_date || '-';
                return `<li><strong>${invoice.invoice_number}</strong> &mdash; jatuh tempo ${dueDateLabel} (${overdueDays} terlambat)</li>`;
            }).join('');
            sections.push(`<p>Customer memiliki Invoice tertunggak:</p><ul>${invoices}</ul>`);
        }

        return sections.join('<hr>');
    };

    // --- Debounce Utility ---
    const debounce = (func, delay) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(func, delay);
    };

    // --- Customer Search Logic ---
    const fetchAndRenderCustomers = async () => {
        const query = customerSearchInput.value;
        if (query.length < 1) {
            customerResultsContainer.classList.remove('is-active');
            return;
        }

        // The backend expects the parameter `q` for searching.
        // Convert the query to lowercase for case-insensitive search on the backend.
        const response = await fetch(`${customersUrl}?q=${query.toLowerCase()}`);
        const result = await response.json();
        // The controller uses Yajra DataTables which returns data in a `data` property.
        const customers = Array.isArray(result.data) ? result.data : [];

        customerResultsContainer.innerHTML = '';
        if (customers.length > 0) {
            customers.forEach(customer => {
                const item = document.createElement('div');
                item.classList.add('result-item');
                item.textContent = customer.name;
                item.dataset.id = customer.id;
                item.dataset.name = customer.name;
                customerResultsContainer.appendChild(item);
            });
            customerResultsContainer.classList.add('is-active');
        } else {
            customerResultsContainer.classList.remove('is-active');
        }
    };

    customerSearchInput.addEventListener('input', () => debounce(fetchAndRenderCustomers, 300));

    customerResultsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('result-item')) {
            const customerId = e.target.dataset.id;
            const customerName = e.target.dataset.name;

            customerSearchInput.value = customerName;
            customerIdInput.value = customerId;
            customerResultsContainer.classList.remove('is-active');

            // Trigger address loading
            loadAddresses(customerId);
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.custom-search-wrapper')) {
            customerResultsContainer.classList.remove('is-active');
        }
    });

    // --- Address & Staff Logic ---
    const loadAddresses = async (customerId) => {
        addressSelect.innerHTML = '<option value="">Loading...</option>';
        addressSelect.disabled = true;
        
        if (!customerId) {
            addressSelect.innerHTML = '<option value="">Pilih Customer terlebih dahulu</option>';
            return;
        }

        const url = addressesUrlTemplate.replace('__CUSTOMER_ID__', customerId);
        const response = await fetch(url);
        const addresses = await response.json();

        addressSelect.innerHTML = '<option value="">Pilih Alamat</option>';
        addresses.forEach(address => {
            const option = new Option(address.full_address, address.id);
            option.dataset.areaName = address.area.name;
            option.dataset.areaId = address.area.id;
            addressSelect.add(option);
        });
        addressSelect.disabled = false;
    };

    addressSelect.addEventListener('change', async function() {
        staffContainer.innerHTML = '';
        availableStaff = [];
        const selectedOption = this.options[this.selectedIndex];

        if (!selectedOption.value) {
            areaNameInput.value = '';
            areaIdInput.value = '';
            addStaffBtn.disabled = true;
            addStaffBtn.textContent = 'Pilih Alamat terlebih dahulu';
            return;
        }
        
        const areaName = selectedOption.dataset.areaName;
        const areaId = selectedOption.dataset.areaId;

        areaNameInput.value = areaName;
        areaIdInput.value = areaId;
        
        const url = staffByAreaUrlTemplate.replace('__AREA_ID__', areaId);
        const response = await fetch(url);
        availableStaff = await response.json();
        addStaffBtn.disabled = false;
        addStaffBtn.textContent = 'Add Staff';
    });

    const select2Ready = window.__select2Ready || Promise.resolve();

    const initServiceSelect = (selectElement) => {
        if (!selectElement) {
            return;
        }

        select2Ready.then(() => {
            if (!(window.$ && $.fn && typeof $.fn.select2 === 'function')) {
                return;
            }

            $(selectElement).select2({
                placeholder: selectElement.dataset.placeholder || 'Pilih Layanan',
                width: '100%',
                dropdownParent: $(selectElement).closest('.service-item'),
            });
        });
    };

    const setServiceButtonState = (state) => {
        if (!addServiceBtn) {
            return;
        }

        switch (state) {
            case 'loading':
                addServiceBtn.disabled = true;
                addServiceBtn.textContent = 'Memuat daftar layanan...';
                break;
            case 'ready':
                addServiceBtn.disabled = false;
                addServiceBtn.textContent = 'Add Service';
                break;
            case 'empty':
                addServiceBtn.disabled = true;
                addServiceBtn.textContent = 'Tidak ada layanan tersedia';
                break;
            default:
                addServiceBtn.disabled = true;
                addServiceBtn.textContent = 'Gagal memuat layanan';
                break;
        }
    };

    setServiceButtonState('loading');

    // --- Service Items Logic ---
    const fetchServices = async () => {
        try {
            const response = await fetch(servicesUrl);
            if (!response.ok) {
                throw new Error('Failed to fetch services');
            }
            const result = await response.json();
            const payload = Array.isArray(result) ? result : (
                result && Array.isArray(result.data) ? result.data : []
            );
            allServices = Array.isArray(payload) ? payload : [];

            if (allServices.length === 0) {
                setServiceButtonState('empty');
                return;
            }

            setServiceButtonState('ready');
        } catch (error) {
            console.error('Failed to fetch services data', error);
            setServiceButtonState('error');
        }
    };

    addServiceBtn.addEventListener('click', () => {
        if (!allServices.length) {
            showWarning('Daftar layanan belum tersedia. Silakan muat ulang halaman.');
            return;
        }

        serviceItemCount++;
        const serviceOptions = allServices.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        const newItem = document.createElement('div');
        newItem.classList.add('service-item', 'mb-3');
        newItem.innerHTML = `
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label mb-1">Layanan</label>
                    <select name="services[new_${serviceItemCount}][service_id]" class="form-select service-select" data-placeholder="Pilih Layanan" required>
                        <option value=""></option>
                        ${serviceOptions}
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label mb-1">Qty</label>
                    <input type="number" name="services[new_${serviceItemCount}][quantity]" class="form-control service-quantity" value="1" min="1" required>
                </div>
                <div class="col-6 col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger w-100 remove-service-item">Remove</button>
                </div>
            </div>
        `;
        serviceItemsContainer.appendChild(newItem);
        initServiceSelect(newItem.querySelector('.service-select'));
    });

    serviceItemsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-service-item')) {
            const item = e.target.closest('.service-item');
            if (item) {
                const select = item.querySelector('.service-select');
                if (select && $(select).data('select2')) {
                    $(select).select2('destroy');
                }
                item.remove();
            }
        }
    });

    // --- Staff Items Logic ---
    addStaffBtn.addEventListener('click', () => {
        if (!areaIdInput.value) {
            alert('Please select an address first to determine the area.');
            return;
        }
        staffMemberCount++;
        const staffOptions = availableStaff.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        const newItem = document.createElement('div');
        newItem.classList.add('input-group', 'mb-2', 'staff-member');
        newItem.innerHTML = `
            <select name="staff[]" class="form-select staff-select">
                 <option value="">Pilih Staff</option>
                ${staffOptions}
            </select>
            <button type="button" class="btn btn-danger remove-staff-member">Remove</button>
        `;
        staffContainer.appendChild(newItem);
    });

    staffContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-staff-member')) {
            e.target.closest('.staff-member').remove();
        }
    });

    // --- Work Time Input Formatting ---
    const formatWorkTime = (value) => {
        const digits = value.replace(/\D/g, '').slice(0, 4);

        if (digits.length <= 2) {
            return digits;
        }

        const hours = digits.slice(0, 2);
        const minutes = digits.slice(2);
        return `${hours}:${minutes}`;
    };

    const enforceWorkTimeFormat = (input) => {
        input.addEventListener('input', (event) => {
            const formatted = formatWorkTime(event.target.value);
            event.target.value = formatted;
        });

        input.addEventListener('blur', (event) => {
            const digits = event.target.value.replace(/\D/g, '').slice(0, 4);
            if (digits.length === 0) {
                event.target.value = '';
                return;
            }
            const hours = digits.slice(0, 2).padEnd(2, '0');
            const minutes = digits.slice(2).padEnd(2, '0');
            event.target.value = `${hours}:${minutes}`;
        });
    };

    document.querySelectorAll('.js-work-time-input').forEach(enforceWorkTimeFormat);

    const checkPendingServiceOrder = async (customerId) => {
        if (!pendingCheckUrlTemplate || !customerId) {
            return { has_pending: false };
        }

        const url = pendingCheckUrlTemplate.replace('__CUSTOMER_ID__', customerId);
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch pending Service Order status');
        }

        return response.json();
    };

    form.addEventListener('submit', async (event) => {
        if (isSubmitting) {
            return;
        }

        event.preventDefault();

        const customerId = customerIdInput.value;

        if (!customerId) {
            showWarning('Silakan pilih customer terlebih dahulu.');
            return;
        }

        try {
            const pendingStatus = await checkPendingServiceOrder(customerId);
            const hasBlockingIssues = pendingStatus.has_pending || pendingStatus.has_overdue_invoices;

            if (hasBlockingIssues) {
                const html = buildBlockingHtml(pendingStatus);
                showWarning({
                    html: html || 'Customer masih memiliki kewajiban yang harus diselesaikan sebelum membuat pesanan baru.',
                });
                return;
            }

            isSubmitting = true;
            form.submit();
        } catch (error) {
            console.error('Pending Service Order validation failed', error);
            showWarning('Gagal memvalidasi status Service Order customer. Silakan coba lagi.');
        }
    });

    // --- Initial Data Fetch ---
    fetchServices();
}
