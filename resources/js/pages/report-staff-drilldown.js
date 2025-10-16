import ApexCharts from 'apexcharts';

$(function() {
    const page = $('#staff-drilldown-page');
    if (!page.length) {
        return;
    }

    const staffId = page.data('staff-id');
    const staffName = page.data('staff-name');
    const workloadUrl = page.data('workload-url');
    const specializationUrl = page.data('specialization-url');
    const tableUrl = page.data('table-url');

    // 1. Get filters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const startDate = urlParams.get('start_date');
    const endDate = urlParams.get('end_date');
    const areaId = urlParams.get('area_id');

    // Display filter info
    $('#filter-display-date').text(`${startDate} to ${endDate}`);

    const ajaxParams = { start_date: startDate, end_date: endDate, area_id: areaId };

    // 2. Render Workload Chart
    $.get(workloadUrl, ajaxParams, function(response) {
        const workloadChartOptions = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [{
                name: 'Jumlah Pekerjaan',
                data: response.data
            }],
            xaxis: {
                categories: response.labels
            },
            title: {
                text: `Beban Kerja Mingguan: ${staffName}`,
                align: 'left'
            },
             plotOptions: {
                bar: {
                    horizontal: false,
                }
            },
        };

        const workloadChart = new ApexCharts(document.querySelector('#chart-staff-workload'), workloadChartOptions);
        workloadChart.render();
    });

    // 3. Render Specialization Chart
    $.get(specializationUrl, ajaxParams, function(response) {
        const specializationChartOptions = {
            chart: {
                type: 'donut',
                height: 350
            },
            series: response.data,
            labels: response.labels,
            title: {
                text: `Spesialisasi Layanan: ${staffName}`,
                align: 'left'
            },
            legend: {
                position: 'bottom'
            }
        };

        const specializationChart = new ApexCharts(document.querySelector('#chart-staff-specialization'), specializationChartOptions);
        specializationChart.render();
    });


    // 4. Initialize DataTable
    $('#staff-drilldown-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: tableUrl,
            data: function(d) {
                d.start_date = startDate;
                d.end_date = endDate;
                d.area_id = areaId;
            }
        },
        columns: [
            { data: 'so_number', name: 'so_number' },
            { data: 'customer_name', name: 'customer.name' },
            { data: 'customer_address', name: 'address.full_address' },
            { data: 'work_date', name: 'work_date' },
            { data: 'invoice_total', name: 'invoice.grand_total' },
            { data: 'status', name: 'status' },
        ],
        order: [[2, 'desc']]
    });
});
