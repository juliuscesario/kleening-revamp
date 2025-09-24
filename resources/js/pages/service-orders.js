// resources/js/pages/service-orders.js
$(function() {
    const ajaxUrl = $('#service-orders-table').data('url');

    $('#service-orders-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: ajaxUrl,
        columns: [
            { data: 'so_number', name: 'so_number' },
            { data: 'customer_name', name: 'customer.name' },
            { data: 'work_date', name: 'work_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
