import DataTable from 'datatables.net-bs5';

$(document).ready(function() {
    new DataTable('#invoices-table', {
        ajax: '/data/invoices',
        processing: true,
        serverSide: true,
        columns: [
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'service_order_id', name: 'service_order_id' },
            { data: 'issue_date', name: 'issue_date' },
            { data: 'due_date', name: 'due_date' },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });
});
