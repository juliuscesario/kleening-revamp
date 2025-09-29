$(function() {
    const page = $('#staff-utilization-report-page');
    if (!page.length) {
        return;
    }

    const staffTable = $('#staff-utilization-table');
    const staffAjaxUrl = staffTable.data('url');

    // Set default dates
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    $('#filter-start-date').val(firstDayOfMonth.toISOString().split('T')[0]);
    $('#filter-end-date').val(today.toISOString().split('T')[0]);

    // --- Staff DataTable ---
    const dataTable = staffTable.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: staffAjaxUrl,
            data: function(d) {
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
                d.area_id = $('#filter-area').val();
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'total_hours_worked', name: 'total_hours_worked' },
            { data: 'utilization_rate', name: 'utilization_rate' },
        ],
        order: [[1, 'desc']]
    });

    // --- Event Listeners ---
    $('#apply-filters').on('click', function() {
        dataTable.ajax.reload();
    });
});
