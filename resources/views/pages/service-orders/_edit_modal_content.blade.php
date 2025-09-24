<div class="modal-body">
    <form id="editServiceOrderForm" method="POST" action="{{ route('web.service-orders.update', $serviceOrder->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Status</label>
            <input type="hidden" name="original_status" value="{{ $serviceOrder->status }}">
            <select name="status" class="form-select" id="service-order-status">
                <option value="dijadwalkan" {{ $serviceOrder->status == 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                <option value="proses" {{ $serviceOrder->status == 'proses' ? 'selected' : '' }}>Proses</option>
                <option value="selesai" {{ $serviceOrder->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="batal" {{ $serviceOrder->status == 'batal' ? 'selected' : '' }}>Batal</option>
                <option value="invoiced" {{ $serviceOrder->status == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
            </select>
        </div>

        <div class="mb-3" id="owner-password-field" style="display: none;">
            <label class="form-label">Owner Password (for Batal)</label>
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
            <label class="form-label">Ordered Services</label>
            <div id="service-items-container">
                @foreach($serviceOrder->items as $item)
                    <div class="input-group mb-2 service-item" data-item-id="{{ $item->id }}">
                        <select name="services[{{ $item->id }}][service_id]" class="form-select service-select">
                            @foreach($allServices as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="services[{{ $item->id }}][quantity]" class="form-control service-quantity" value="{{ $item->quantity }}" min="1">
                        <button type="button" class="btn btn-danger remove-service-item">Remove</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-success mt-2" id="add-service-item">Add Service</button>
        </div>

        <div class="mb-3">
            <label class="form-label">Assigned Staff</label>
            <div id="staff-container">
                @foreach($serviceOrder->staff as $staffMember)
                    <div class="input-group mb-2 staff-member" data-staff-id="{{ $staffMember->id }}">
                        <select name="staff[]" class="form-select staff-select">
                            @foreach($allStaff as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-danger remove-staff-member">Remove</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-success mt-2" id="add-staff-member">Add Staff</button>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Service Items
        let serviceItemCount = {{ count($serviceOrder->items) }};
        document.getElementById('add-service-item').addEventListener('click', function () {
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
                <input type="number" name="services[new_${serviceItemCount}][quantity]" class="form-control service-quantity" value="1" min="1">
                <button type="button" class="btn btn-danger remove-service-item">Remove</button>
            `;
            container.appendChild(newItem);
        });

        document.getElementById('service-items-container').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-service-item')) {
                e.target.closest('.service-item').remove();
            }
        });

        // Staff Members
        let staffMemberCount = {{ count($serviceOrder->staff) }};
        document.getElementById('add-staff-member').addEventListener('click', function () {
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

        document.getElementById('staff-container').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-staff-item')) {
                e.target.closest('.staff-member').remove();
            }
        });

        // Form submission via AJAX
        document.getElementById('editServiceOrderForm').addEventListener('submit', function (e) {
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

        // Status transition logic
        const statusSelect = document.getElementById('service-order-status');
        const originalStatus = document.querySelector('input[name="original_status"]').value;
        const ownerPasswordField = document.getElementById('owner-password-field');

        function applyStatusLogic() {
            const currentSelectedStatus = statusSelect.value;

            // Reset all options to enabled first
            Array.from(statusSelect.options).forEach(option => {
                option.disabled = false;
            });

            if (originalStatus === 'dijadwalkan') {
                // Can go to 'proses' or 'batal'
                Array.from(statusSelect.options).forEach(option => {
                    if (option.value !== 'dijadwalkan' && option.value !== 'proses' && option.value !== 'batal') {
                        option.disabled = true;
                    }
                });
            } else if (originalStatus === 'proses') {
                // Can go to 'batal' or 'selesai'
                Array.from(statusSelect.options).forEach(option => {
                    if (option.value !== 'proses' && option.value !== 'batal' && option.value !== 'selesai') {
                        option.disabled = true;
                    }
                });
            } else if (originalStatus === 'batal' || originalStatus === 'selesai') {
                // Cannot change from 'batal' or 'selesai'
                Array.from(statusSelect.options).forEach(option => {
                    if (option.value !== originalStatus) {
                        option.disabled = true;
                    }
                });
            }

            // Show/hide owner password field for 'proses' to 'batal' transition
            if (originalStatus === 'proses' && currentSelectedStatus === 'batal') {
                ownerPasswordField.style.display = 'block';
            } else {
                ownerPasswordField.style.display = 'none';
            }
        }

        // Apply logic on load
        applyStatusLogic();

        // Apply logic on status change
        statusSelect.addEventListener('change', applyStatusLogic);
    });
</script>