$(function() {
    let table = $('#staff-performance-report-table');
    let dataTable = table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: table.data('url'),
            data: function (d) {
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
                d.area_id = $('#filter-area').val();
                d.staff_id = $('#filter-staff').val();
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'area_name', name: 'area.name', orderable: false },
            { data: 'jobs_completed', name: 'jobs_completed', searchable: false, orderable: false },
            { data: 'total_revenue', name: 'total_revenue', searchable: false, orderable: false }
        ],
        order: [[0, 'asc']] // Default order by name
    });

    $('#apply-filters').on('click', function() {
        dataTable.ajax.reload();
    });
});
