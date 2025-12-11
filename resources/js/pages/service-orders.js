// resources/js/pages/service-orders.js

const normalizeDateInput = (value) => {
    const digits = value.replace(/\D/g, '').slice(0, 8);
    if (digits.length <= 2) return digits;
    if (digits.length <= 4) {
        return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    }
    const day = digits.slice(0, 2);
    const month = digits.slice(2, 4);
    const year = digits.slice(4);
    return `${day}/${month}/${year}`;
};

const formatDateForServer = (value) => {
    if (!value) return '';
    const parts = value.split('/');
    if (parts.length !== 3) return '';
    const [day, month, year] = parts;
    if (day.length !== 2 || month.length !== 2 || year.length !== 4) return '';
    return `${year}-${month}-${day}`;
};

const normalizeTimeInput = (value) => {
    const digits = value.replace(/\D/g, '').slice(0, 4);
    if (digits.length <= 2) return digits;
    return `${digits.slice(0, 2)}:${digits.slice(2)}`;
};

const formatTimeForServer = (value) => {
    if (!value) return '';
    const parts = value.split(':');
    if (parts.length !== 2) return '';
    const [hours, minutes] = parts;
    if (hours.length !== 2 || minutes.length !== 2) return '';
    const hourInt = parseInt(hours, 10);
    const minuteInt = parseInt(minutes, 10);
    if (isNaN(hourInt) || hourInt < 0 || hourInt > 23) return '';
    if (isNaN(minuteInt) || minuteInt < 0 || minuteInt > 59) return '';
    return `${hours}:${minutes}`;
};

const ajaxUrl = $('#service-orders-table').data('url');
const updateUrlTemplate = $('#service-orders-table').data('update-url-template');

$(function() {
    document.querySelectorAll('.js-filter-date').forEach((input) => {
        input.addEventListener('input', (event) => {
            event.target.value = normalizeDateInput(event.target.value);
        });

        input.addEventListener('blur', (event) => {
            const normalized = normalizeDateInput(event.target.value);
            if (normalized.length === 10) {
                event.target.value = normalized;
            } else {
                event.target.value = '';
            }
        });
    });

    document.querySelectorAll('.js-filter-time').forEach((input) => {
        input.addEventListener('input', (event) => {
            event.target.value = normalizeTimeInput(event.target.value);
        });

        input.addEventListener('blur', (event) => {
            const formatted = formatTimeForServer(event.target.value);
            event.target.value = formatted;
        });
    });

    let serviceOrdersTable = $('#service-orders-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: ajaxUrl, // Explicitly set the URL here
            data: function (d) {
                d.status = $('#service-orders-table').data('current-status-filter');
                d.start_date = formatDateForServer($('#filter-start-date').val());
                d.start_time = formatTimeForServer($('#filter-start-time').val());
                d.end_date = formatDateForServer($('#filter-end-date').val());
                d.end_time = formatTimeForServer($('#filter-end-time').val());
            }
        },
        columns: [
            { data: 'so_number', name: 'so_number' },
            { data: 'customer_name', name: 'customer.name' },
            { data: 'customer_phone', name: 'customer.phone_number', orderable: false },
            { data: 'work_date', name: 'work_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Handle status change button clicks
    $('#service-orders-table').on('click', '.change-status-btn', function() {
        const serviceOrderId = $(this).data('id');
        const newStatus = $(this).data('new-status');
        const currentStatus = $(this).closest('tr').find('span.badge').text().toLowerCase(); // Get current status from badge
        let ownerPassword = null;

        const updateUrl = updateUrlTemplate.replace('__SERVICE_ORDER_ID__', serviceOrderId);

        const performStatusUpdate = () => {
            const requestData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status: newStatus,
            };

            if (ownerPassword !== null) {
                requestData.owner_password = ownerPassword;
            }

            $.ajax({
                url: updateUrl,
                method: 'PUT',
                data: requestData,
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Status updated successfully!');
                        serviceOrdersTable.ajax.reload(); // Use the variable here
                    } else {
                        alert(response.message || 'Failed to update status.');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred.';
                    alert(errorMsg);
                }
            });
        };

        if (currentStatus === 'proses' && newStatus === 'cancelled') {
            // Prompt for owner password
            Swal.fire({
                title: 'Konfirmasi Pembatalan',
                html: 'Untuk membatalkan pesanan dari status \'proses\', masukkan password owner:',
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Konfirmasi',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    ownerPassword = password;
                    return true; // Proceed to AJAX call
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    performStatusUpdate();
                }
            });
        } else {
            // For other transitions, confirm directly
            if (confirm(`Anda yakin ingin mengubah status menjadi ${newStatus}?`)) {
                performStatusUpdate();
            }
        }
    });

    // Handle status filter button clicks
    $('.filter-status-btn').on('click', function() {
        const statusFilter = $(this).data('status');
        $('#service-orders-table').data('current-status-filter', statusFilter);
        serviceOrdersTable.ajax.reload();

        // Update active state of filter buttons
        $('.filter-status-btn').removeClass('btn-primary btn-info btn-warning btn-success btn-danger btn-secondary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
    });

    // Initialize active filter button
    $('.filter-status-btn[data-status="all"]').addClass('btn-primary').removeClass('btn-outline-primary');

    // Date & Time Filter
    $('#apply-date-filter').on('click', function() {
        serviceOrdersTable.ajax.reload();
    });

    $('#reset-date-filter').on('click', function() {
        $('#filter-start-date').val('');
        $('#filter-start-time').val('');
        $('#filter-end-date').val('');
        $('#filter-end-time').val('');
        serviceOrdersTable.ajax.reload();
    });

    // Handle create invoice button click
    $('#service-orders-table').on('click', '.create-invoice', function() {
        const serviceOrderId = $(this).data('id');
        window.location.href = `/invoices/create?service_order_id=${serviceOrderId}`;
    });
});
