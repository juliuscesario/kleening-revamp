<div class="modal-body">
    <form id="editServiceOrderForm" method="POST" action="{{ route('web.service-orders.update', $serviceOrder->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Status</label>
            <input type="hidden" name="original_status" value="{{ $serviceOrder->status }}">
            <select name="status" class="form-select" id="service-order-status">
                <option value="booked" {{ $serviceOrder->status == 'booked' ? 'selected' : '' }}>Booked</option>
                <option value="proses" {{ $serviceOrder->status == 'proses' ? 'selected' : '' }}>Proses</option>
                <option value="done" {{ $serviceOrder->status == 'done' ? 'selected' : '' }}>Done</option>
                <option value="cancelled" {{ $serviceOrder->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="invoiced" {{ $serviceOrder->status == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
            </select>
        </div>

        <div class="mb-3" id="owner-password-field" style="display: none;">
            <label class="form-label">Owner Password (for Cancelled)</label>
            <input type="password" name="owner_password" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Work Notes</label>
            <textarea name="work_notes" class="form-control" rows="3">{{ $serviceOrder->work_notes }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Staff Notes</label>
            <textarea name="staff_notes" class="form-control" rows="3">{{ $serviceOrder->staff_notes }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Waktu Pengerjaan (WIB)</label>
            <input type="text"
                name="work_time"
                class="form-control js-work-time-input"
                inputmode="numeric"
                pattern="^([01]\d|2[0-3]):[0-5]\d$"
                placeholder="00:00"
                required
                value="{{ old('work_time', $serviceOrder->work_time_formatted) }}">
            <small class="form-text text-muted">Gunakan format 24 jam, contoh 09:15 atau 18:30.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Ordered Services</label>
            <div id="service-items-container">
                @foreach($serviceOrder->items as $item)
                    <div class="input-group mb-2 service-item" data-item-id="{{ $item->id }}">
                        <select name="services[{{ $item->id }}][service_id]" class="form-select service-select">
                            @foreach($allServices as $service)
                                <option value="{{ $service->id }}" {{ $item->service_id == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                            @endforeach
                        </select>
                        <span class="input-group-text">Qty</span>
                        <input type="number" name="services[{{ $item->id }}][quantity]" class="form-control service-quantity" value="{{ $item->quantity ?? 1 }}" min="1">
                        <button type="button" class="btn btn-danger remove-service-item">Remove</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-success mt-2 w-100" id="add-service-item">Add Service</button>
        </div>

        <div class="mb-3">
            <label class="form-label">Assigned Staff</label>
            <div id="staff-container">
                @foreach($serviceOrder->staff as $staffMember)
                    <div class="input-group mb-2 staff-member" data-staff-id="{{ $staffMember->id }}">
                        <select name="staff[]" class="form-select staff-select">
                            @foreach($allStaff as $staff)
                                <option value="{{ $staff->id }}" {{ $staffMember->id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-danger remove-staff-member">Remove</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-success mt-2 w-100" id="add-staff-member">Add Staff</button>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formatWorkTime = (value) => {
            const digits = value.replace(/\D/g, '').slice(0, 4);
            if (digits.length <= 2) {
                return digits;
            }
            return `${digits.slice(0, 2)}:${digits.slice(2)}`;
        };

        const enforceWorkTimeFormat = (input) => {
            input.addEventListener('input', (event) => {
                event.target.value = formatWorkTime(event.target.value);
            });

            input.addEventListener('blur', (event) => {
                const digits = event.target.value.replace(/\D/g, '').slice(0, 4);
                if (!digits.length) {
                    event.target.value = '';
                    return;
                }
                const hours = digits.slice(0, 2).padEnd(2, '0');
                const minutes = digits.slice(2).padEnd(2, '0');
                event.target.value = `${hours}:${minutes}`;
            });
        };

        document.querySelectorAll('.js-work-time-input').forEach(enforceWorkTimeFormat);

        const addServiceItemButton = document.getElementById('add-service-item');
        if (addServiceItemButton) {
            // Service Items
            let serviceItemCount = {{ count($serviceOrder->items) }};
            addServiceItemButton.addEventListener('click', function () {
                serviceItemCount++;
                const container = document.getElementById('service-items-container');
                const newItem = document.createElement('div');
                newItem.classList.add('input-group', 'mb-2', 'service-item');
                newItem.innerHTML = `
                    <select name="services[new_${serviceItemCount}][service_id]" class="form-select service-select">
                        @foreach($allServices as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                    <span class="input-group-text">Qty</span>
                    <input type="number" name="services[new_${serviceItemCount}][quantity]" class="form-control service-quantity" value="1" min="1">
                    <button type="button" class="btn btn-danger remove-service-item">Remove</button>
                `;
                container.appendChild(newItem);
            });
        }

        const serviceItemsContainer = document.getElementById('service-items-container');
        if (serviceItemsContainer) {
            serviceItemsContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-service-item')) {
                    e.target.closest('.service-item').remove();
                }
            });
        }

        const addStaffMemberButton = document.getElementById('add-staff-member');
        if (addStaffMemberButton) {
            // Staff Members
            let staffMemberCount = {{ count($serviceOrder->staff) }};
            addStaffMemberButton.addEventListener('click', function () {
                staffMemberCount++;
                const container = document.getElementById('staff-container');
                const newItem = document.createElement('div');
                newItem.classList.add('input-group', 'mb-2', 'staff-member');
                newItem.innerHTML = `
                    <select name="staff[]" class="form-select staff-select">
                        @foreach($allStaff as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-danger remove-staff-member">Remove</button>
                `;
                container.appendChild(newItem);
            });
        }

        const staffContainer = document.getElementById('staff-container');
        if (staffContainer) {
            staffContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-staff-member')) {
                    e.target.closest('.staff-member').remove();
                }
            });
        }

        const editServiceOrderForm = document.getElementById('editServiceOrderForm');
        if (editServiceOrderForm) {
            // Form submission via AJAX
            editServiceOrderForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const form = e.target;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Service Order updated successfully!');
                        location.reload();
                    } else {
                        alert('Error updating Service Order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the Service Order.');
                });
            });
        }

        // Status transition logic
        const statusSelect = document.getElementById('service-order-status');
        if (statusSelect) {
            const originalStatus = document.querySelector('input[name="original_status"]').value;
            const ownerPasswordField = document.getElementById('owner-password-field');

            function applyStatusLogic() {
                const currentSelectedStatus = statusSelect.value;

                // Reset all options to enabled first
                Array.from(statusSelect.options).forEach(option => {
                    option.disabled = false;
                });

                if (originalStatus === 'booked') {
                    // Can go to 'proses' or 'cancelled'
                    Array.from(statusSelect.options).forEach(option => {
                        if (option.value !== 'booked' && option.value !== 'proses' && option.value !== 'cancelled') {
                            option.disabled = true;
                        }
                    });
                } else if (originalStatus === 'proses') {
                    // Can go to 'cancelled' or 'done'
                    Array.from(statusSelect.options).forEach(option => {
                        if (option.value !== 'proses' && option.value !== 'cancelled' && option.value !== 'done') {
                            option.disabled = true;
                        }
                    });
                } else if (originalStatus === 'invoiced' || originalStatus === 'cancelled' || originalStatus === 'done') {
                    // Cannot change from 'invoiced', 'cancelled' or 'done'
                    Array.from(statusSelect.options).forEach(option => {
                        if (option.value !== originalStatus) {
                            option.disabled = true;
                        }
                    });
                }

                // Show/hide owner password field for 'proses' to 'cancelled' transition
                if (originalStatus === 'proses' && currentSelectedStatus === 'cancelled') {
                    ownerPasswordField.style.display = 'block';
                } else {
                    ownerPasswordField.style.display = 'none';
                }
            }

            // Apply logic on load
            applyStatusLogic();

            // Apply logic on status change
            statusSelect.addEventListener('change', applyStatusLogic);
        }
    });
</script>
