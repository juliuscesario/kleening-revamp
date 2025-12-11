$(function() {
    const table = $('#revenue-report-table');
    const ajaxUrl = table.data('url');

    // Set default dates
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    $('#filter-start-date').val(firstDayOfMonth.toISOString().split('T')[0]);
    $('#filter-end-date').val(today.toISOString().split('T')[0]);

    const dataTable = table.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: ajaxUrl,
            data: function(d) {
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
                d.area_id = $('#filter-area').val();
            }
        },
        columns: [
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const drilldownBaseUrl = table.data('drilldown-url');
                        if (!drilldownBaseUrl) return data;

                        let drilldownUrl = new URL(drilldownBaseUrl.replace('__ID__', row.id), window.location.origin);
                        drilldownUrl.searchParams.append('start_date', $('#filter-start-date').val());
                        drilldownUrl.searchParams.append('end_date', $('#filter-end-date').val());
                        drilldownUrl.searchParams.append('area_id', $('#filter-area').val() || 'all');
                        
                        // Pass area name for display on drilldown page if a specific area is selected
                        if ($('#filter-area').val() && $('#filter-area').val() !== 'all') {
                             drilldownUrl.searchParams.append('area_name', $('#filter-area option:selected').text());
                        }

                        return `<a href="${drilldownUrl.toString()}">${data}</a>`;
                    }
                    return data;
                }
            },
            { data: 'total_orders', name: 'total_orders' },
            { data: 'total_revenue', name: 'total_revenue' },
        ],
        drawCallback: function(settings) {
            const summary = settings.json.summary;
            if (summary) {
                $('#summary-total-revenue').text(summary.total_revenue);
                $('#summary-total-orders').text(summary.total_orders);
                $('#summary-avg-revenue').text(summary.avg_revenue);
            }
        }
    });

    $('#apply-filters').on('click', function() {
        dataTable.ajax.reload();
    });
});
