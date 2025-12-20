// resources/js/pages/invoices.js

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

$(function () {
    // Date Input Masking
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

    const table = $('#invoices-table').DataTable({
        ajax: {
            url: '/data/invoices',
            data: function (d) {
                d.status = $('#invoices-table').data('current-status-filter');
                d.start_date = formatDateForServer($('#filter-start-date').val());
                d.end_date = formatDateForServer($('#filter-end-date').val());
            },
            dataSrc: function (json) {
                return json.data;
            }
        },
        processing: true,
        serverSide: true,
        responsive: true,
        destroy: true, // Keeping this to avoid re-init issues if any
        columns: [
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'so_number', name: 'serviceOrder.so_number' },
            { data: 'customer_name', name: 'customer_name', orderable: false, searchable: false },
            { data: 'customer_phone', name: 'customer_phone', orderable: false, searchable: false },
            { data: 'issue_date', name: 'issue_date' },
            { data: 'due_date', name: 'due_date' },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'balance', name: 'balance' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']]
    });

    // Handle status filter button clicks
    $('.filter-status-btn').on('click', function () {
        const statusFilter = $(this).data('status');
        $('#invoices-table').data('current-status-filter', statusFilter);
        table.ajax.reload();

        // Update active state of filter buttons
        $('.filter-status-btn').removeClass('btn-primary btn-info btn-warning btn-success btn-danger btn-secondary').addClass('btn-outline-primary');

        // Add appropriate class based on status
        $(this).removeClass('btn-outline-primary');
        switch (statusFilter) {
            case 'new': $(this).addClass('btn-info'); break;
            case 'sent': $(this).addClass('btn-warning'); break;
            case 'overdue': $(this).addClass('btn-danger'); break;
            case 'paid': $(this).addClass('btn-success'); break;
            case 'cancelled': $(this).addClass('btn-secondary'); break;
            default: $(this).addClass('btn-primary'); // for 'all'
        }
    });

    // Initialize active filter button
    // Actually the HTML comes with 'All' as primary by default in my edit?
    // Let's ensure 'All' is active initially purely via JS if needed, but the HTML has 'btn-primary' on 'All'.
    // The previous code had specific button classes. 
    // In my View edit: 'All' is btn-primary, 'New' is btn-outline-info etc.
    // The logic above resets everything to btn-outline-primary then adds class.
    // That logic assumes all buttons start as outline-primary.
    // But 'All' starts as 'btn-primary'.
    // So the reset line `removeClass(...)` is fine, it will strip `btn-primary`.
    // Then I need to make sure I re-add the correct class.
    // My switch case handles it.

    // Also, the View has specific outline classes initially (e.g. btn-outline-info).
    // The reset logic removes ALL color classes and adds btn-outline-primary.
    // This effectively makes them all blue outlines when inactive. 
    // This is slightly different from `service-orders` where they might stay colored outlines.
    // Let's check service-orders.js again.
    // It says: `$('.filter-status-btn').removeClass('btn-primary ...').addClass('btn-outline-primary');`
    // This suggests they ALL become blue outlines when inactive. That's fine.

    // Date Filter
    $('#apply-date-filter').on('click', function () {
        table.ajax.reload();
    });

    $('#reset-date-filter').on('click', function () {
        $('#filter-start-date').val('');
        $('#filter-end-date').val('');
        table.ajax.reload();
    });

    $('#invoices-table').on('click', '.change-status-btn', function () {
        const invoiceId = $(this).data('id');
        const newStatus = $(this).data('new-status');

        if (newStatus === 'paid') {
            const form = $('#markAsPaidForm');
            const amountInput = form.find('input[name="amount"]');
            const balance = $(this).data('balance');

            form.find('#invoice_id').val(invoiceId);
            amountInput.val(typeof balance !== 'undefined' ? balance : '');

            const modal = new bootstrap.Modal(document.getElementById('markAsPaidModal'));
            modal.show();
        } else {
            if (confirm(`Are you sure you want to change the status to ${newStatus}?`)) {
                $.ajax({
                    url: `/invoices/${invoiceId}/status`,
                    method: 'PUT',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        status: newStatus,
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(response.message || 'Status updated successfully!');
                            table.ajax.reload();
                        } else {
                            alert(response.message || 'Failed to update status.');
                        }
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred.';
                        alert(errorMsg);
                    }
                });
            }
        }
    });

    $('#savePaymentBtn').on('click', function () {
        const form = $('#markAsPaidForm');
        // const invoiceId = form.find('#invoice_id').val(); // unused variable

        $.ajax({
            url: '/payments',
            method: 'POST',
            data: form.serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            success: function (response) {
                if (response.success) {
                    alert('Payment created successfully!');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('markAsPaidModal'));
                    modal.hide();
                    table.ajax.reload();
                } else {
                    alert(response.message || 'Failed to create payment.');
                }
            },
            error: function (xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred.';
                alert(errorMsg);
            }
        });
    });
});
