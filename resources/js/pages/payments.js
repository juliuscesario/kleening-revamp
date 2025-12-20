// resources/js/pages/payments.js
// Importing DataTable is handled via global shim or simple jquery call in other files, 
// checking previous simple file: `import DataTable from 'datatables.net-bs5';`
// I will keep using `import` but will ensure jQuery `$` usage is consistent.

import DataTable from 'datatables.net-bs5';

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

$(document).ready(function () {
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

    const table = $('#payments-table').DataTable({
        ajax: {
            url: '/data/payments',
            data: function (d) {
                d.start_date = formatDateForServer($('#filter-start-date').val());
                d.end_date = formatDateForServer($('#filter-end-date').val());
                d.payment_method = $('#payments-table').data('current-method-filter');
            },
            dataSrc: function (json) {
                // Update Summary Cards
                if (json.summary) {
                    $('#summary-total-revenue').text(json.summary.total_revenue);
                    $('#summary-total-transactions').text(json.summary.total_transactions);
                    $('#summary-avg-transaction').text(json.summary.avg_transaction);
                }
                return json.data;
            }
        },
        processing: true,
        serverSide: true,
        responsive: true,
        destroy: true,
        columns: [
            { data: 'invoice_number', name: 'invoice.invoice_number' },
            { data: 'payment_date', name: 'payment_date' },
            { data: 'amount', name: 'amount' },
            { data: 'payment_method', name: 'payment_method' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[1, 'desc']] // Sort by payment_date desc by default
    });

    // Handle Filter Button
    $('#apply-filter').on('click', function () {
        table.ajax.reload();
    });

    $('#reset-filter').on('click', function () {
        $('#filter-start-date').val('');
        $('#filter-end-date').val('');
        $('#payments-table').data('current-method-filter', 'all');

        // Reset method buttons
        $('.filter-method-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $('.filter-method-btn[data-method="all"]').removeClass('btn-outline-primary').addClass('btn-primary');

        table.ajax.reload();
    });

    // Handle Payment Method Filter
    $('.filter-method-btn').on('click', function () {
        const method = $(this).data('method');
        $('#payments-table').data('current-method-filter', method);

        // Update active state
        $('.filter-method-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');

        table.ajax.reload();
    });
});
