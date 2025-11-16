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

    // --- State ---
    let allServices = [];
    let availableStaff = [];
    let serviceItemCount = 0;
    let staffMemberCount = 0;
    let debounceTimer;

    // --- URLs ---
    const customersUrl = form.dataset.customersUrl;
    const servicesUrl = form.dataset.servicesUrl;
    const addressesUrlTemplate = form.dataset.addressesUrlTemplate;
    const staffByAreaUrlTemplate = form.dataset.staffByAreaUrlTemplate;

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
        const response = await fetch(`${customersUrl}?q=${query}`);
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

    // --- Service Items Logic ---
    const fetchServices = async () => {
        const response = await fetch(servicesUrl);
        const result = await response.json();
        allServices = Array.isArray(result) ? result : result.data;
    };

    addServiceBtn.addEventListener('click', () => {
        serviceItemCount++;
        const serviceOptions = allServices.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        const newItem = document.createElement('div');
        newItem.classList.add('input-group', 'mb-2', 'service-item');
        newItem.innerHTML = `
            <select name="services[new_${serviceItemCount}][service_id]" class="form-select service-select" required>
                <option value="">Pilih Layanan</option>
                ${serviceOptions}
            </select>
            <span class="input-group-text">Qty</span>
            <input type="number" name="services[new_${serviceItemCount}][quantity]" class="form-control service-quantity" value="1" min="1" required>
            <button type="button" class="btn btn-danger remove-service-item">Remove</button>
        `;
        serviceItemsContainer.appendChild(newItem);
    });

    serviceItemsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-service-item')) {
            e.target.closest('.service-item').remove();
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

    // --- Initial Data Fetch ---
    fetchServices();
}
