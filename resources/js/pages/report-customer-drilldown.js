import ApexCharts from 'apexcharts';

$(function() {
    const page = $('#customer-drilldown-page');
    if (!page.length) {
        return;
    }

    const customerId = page.data('customer-id');
    const timelineUrl = page.data('timeline-url');
    const metricsUrl = page.data('metrics-url');
    const frequencyUrl = page.data('frequency-url');
    const historyUrl = page.data('history-url');

    // 1. Populate Key Metrics
    $.get(metricsUrl, function(response) {
        $('#metric-total-spent').text(response.total_spent);
        $('#metric-total-orders').text(response.total_orders);
        $('#metric-avg-days').text(response.avg_days_between_orders);
        $('#metric-frequent-service').text(response.most_frequent_service);
    });

    // 2. Render Spending Timeline Chart
    $.get(timelineUrl, function(response) {
        const timelineChartOptions = {
            chart: {
                type: 'area',
                height: 350,
                zoom: { enabled: false }
            },
            series: [{
                name: 'Total Belanja',
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
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            title: {
                text: 'Linimasa Belanja per Bulan',
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

        const timelineChart = new ApexCharts(document.querySelector('#chart-spending-timeline'), timelineChartOptions);
        timelineChart.render();
    });

    // 3. Initialize Service Frequency Table
    $('#service-frequency-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: false,
        lengthChange: false,
        ajax: frequencyUrl,
        columns: [
            { data: 'service_name', name: 'service_name' },
            { data: 'count', name: 'count' },
        ],
        order: [[1, 'desc']]
    });

    // 4. Initialize Order History Table
    $('#order-history-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: historyUrl,
        columns: [
            { data: 'so_number', name: 'so_number' },
            { data: 'work_date', name: 'work_date' },
            { data: 'area', name: 'address.area.name' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[1, 'desc']]
    });
});
