import ApexCharts from 'apexcharts';

$(function() {
    const page = $('#profitability-report-page');
    if (!page.length) {
        return;
    }

    const serviceTable = $('#profit-by-service-table');
    const serviceAjaxUrl = serviceTable.data('url');

    const areaChartEl = $('#chart-profit-by-area');
    const areaAjaxUrl = areaChartEl.data('url');

    // Set default dates
    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    $('#filter-start-date').val(firstDayOfMonth.toISOString().split('T')[0]);
    $('#filter-end-date').val(today.toISOString().split('T')[0]);

    // --- Service DataTable --- 
    const dataTable = serviceTable.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: serviceAjaxUrl,
            data: function(d) {
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
                d.area_id = $('#filter-area').val();
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'total_revenue', name: 'total_revenue' },
            { data: 'total_cost', name: 'total_cost' },
            { data: 'total_profit', name: 'total_profit' },
        ],
        order: [[3, 'desc']]
    });

    // --- Area Chart --- 
    let areaChart = null;

    function renderAreaChart() {
        $.get(areaAjaxUrl, {
            start_date: $('#filter-start-date').val(),
            end_date: $('#filter-end-date').val(),
            area_id: $('#filter-area').val(),
        }, function(response) {
            const options = {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Total Keuntungan',
                    data: response.map(item => item.total_profit)
                }],
                xaxis: {
                    categories: response.map(item => item.name)
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        distributed: true
                    }
                },
                legend: {
                    show: false
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            };

            if (areaChart) {
                areaChart.destroy();
            }
            
            areaChart = new ApexCharts(areaChartEl[0], options);
            areaChart.render();
        });
    }

    // --- Event Listeners --- 
    $('#apply-filters').on('click', function() {
        dataTable.ajax.reload();
        renderAreaChart();
    });

    // Initial load
    renderAreaChart();
});