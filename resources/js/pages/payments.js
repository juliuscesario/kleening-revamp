import DataTable from 'datatables.net-bs5';

$(document).ready(function() {
    new DataTable('#payments-table', {
        ajax: '/data/payments',
        processing: true,
        serverSide: true,
        columns: [
            { data: 'invoice_number', name: 'invoice.invoice_number' },
            { data: 'payment_date', name: 'payment_date' },
            { data: 'amount', name: 'amount' },
            { data: 'payment_method', name: 'payment_method' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });
});
