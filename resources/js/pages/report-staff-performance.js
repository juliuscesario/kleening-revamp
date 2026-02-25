$(function () {
    let table = $('#staff-performance-report-table');

    // Set default dates
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    $('#filter-start-date').val(firstDayOfMonth.toISOString().split('T')[0]);
    $('#filter-end-date').val(lastDayOfMonth.toISOString().split('T')[0]);

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
            {
                data: 'name',
                name: 'name',
                render: function (data, type, row) {
                    if (type === 'display') {
                        const drilldownBaseUrl = table.data('drilldown-url');
                        if (!drilldownBaseUrl) return data;

                        let drilldownUrl = new URL(drilldownBaseUrl.replace('__ID__', row.id), window.location.origin);
                        drilldownUrl.searchParams.append('start_date', $('#filter-start-date').val());
                        drilldownUrl.searchParams.append('end_date', $('#filter-end-date').val());
                        drilldownUrl.searchParams.append('area_id', $('#filter-area').val() || 'all');

                        return `<a href="${drilldownUrl.toString()}">${data}</a>`;
                    }
                    return data;
                }
            },
            { data: 'area_name', name: 'area.name', orderable: false },
            { data: 'jobs_completed', name: 'jobs_completed', searchable: false, orderable: false },
            { data: 'total_revenue', name: 'total_revenue', searchable: false, orderable: false }
        ],
        pageLength: 25,
        order: [[0, 'asc']] // Default order by name
    });

    $('#apply-filters').on('click', function () {
        dataTable.ajax.reload();
    });
});
