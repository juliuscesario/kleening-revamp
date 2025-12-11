import ApexCharts from 'apexcharts';

$(function() {
    const page = $('#revenue-drilldown-page');
    if (!page.length) {
        return;
    }

    const categoryId = page.data('category-id');
    const categoryName = page.data('category-name');
    const trendUrl = page.data('trend-url');
    const areaUrl = page.data('area-url');
    const tableUrl = page.data('table-url');

    // 1. Get filters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const startDate = urlParams.get('start_date');
    const endDate = urlParams.get('end_date');
    const areaId = urlParams.get('area_id');
    const areaName = urlParams.get('area_name'); // Assuming we might pass this

    // Display filter info
    $('#filter-display-date').text(`${startDate} to ${endDate}`);
    if (areaName) {
        $('#filter-display-area').text(areaName);
    }

    const ajaxParams = { start_date: startDate, end_date: endDate, area_id: areaId };

    // 2. Render Trend Chart
    $.get(trendUrl, ajaxParams, function(response) {
        const trendChartOptions = {
            chart: {
                type: 'area',
                height: 350,
                zoom: {
                    enabled: false
                }
            },
            series: [{
                name: 'Pendapatan',
                data: response.data
            }],
            xaxis: {
                categories: response.labels
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: `Tren Pendapatan Harian: ${categoryName}`,
                align: 'left'
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy'
                },
                y: {
                    formatter: function (value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        };

        const trendChart = new ApexCharts(document.querySelector('#chart-revenue-trend'), trendChartOptions);
        trendChart.render();
    });

    // 3. Render Area Chart (if element exists)
    const areaChartEl = document.querySelector('#chart-revenue-area');
    if (areaChartEl) {
        $.get(areaUrl, ajaxParams, function(response) {
            const areaChartOptions = {
                chart: {
                    type: 'bar',
                    height: 350
                },
                series: [{
                    name: 'Total Pendapatan',
                    data: response.data
                }],
                xaxis: {
                    categories: response.labels
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
                title: {
                    text: `Pendapatan per Area: ${categoryName}`,
                    align: 'left'
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            };

            const areaChart = new ApexCharts(areaChartEl, areaChartOptions);
            areaChart.render();
        });
    }

    // 4. Initialize DataTable
    $('#revenue-drilldown-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: tableUrl,
            data: function(d) {
                d.start_date = startDate;
                d.end_date = endDate;
                d.area_id = areaId;
            }
        },
        columns: [
            { data: 'so_number', name: 'serviceOrder.so_number' },
            { data: 'customer_name', name: 'serviceOrder.customer.name' },
            { data: 'work_date', name: 'work_date' },
            { data: 'service_name', name: 'service.name' },
            { data: 'total', name: 'total' }
        ],
        order: [[2, 'desc']]
    });
});
