$(function() {
    let table = $('#customer-growth-report-table');
    let dataTable = table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: table.data('url'),
            data: function (d) {
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
                d.area_id = $('#filter-area').val();
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'total_orders', name: 'total_orders' },
            { data: 'total_revenue', name: 'total_revenue' }
        ],
        order: [[2, 'desc']] // Order by revenue by default
    });

    $('#apply-filters').on('click', function() {
        dataTable.ajax.reload();
    });
});
