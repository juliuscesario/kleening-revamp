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
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const drilldownBaseUrl = table.data('drilldown-url');
                        if (!drilldownBaseUrl) return data;

                        let drilldownUrl = new URL(drilldownBaseUrl.replace('__ID__', row.id), window.location.origin);
                        return `<a href="${drilldownUrl.toString()}">${data}</a>`;
                    }
                    return data;
                }
            },
            { data: 'total_orders', name: 'total_orders' },
            { data: 'total_revenue', name: 'total_revenue' },
            { data: 'total_cancelled_revenue_potential', name: 'total_cancelled_revenue_potential' },
            { data: 'total_invoice_overdue', name: 'total_invoice_overdue' }
        ],
        order: [[2, 'desc']] // Order by revenue by default
    });

    $('#apply-filters').on('click', function() {
        dataTable.ajax.reload();
    });
});
