import ApexCharts from 'apexcharts';

$(function () {
    const page = $('#staff-drilldown-page');
    if (!page.length) {
        return;
    }

    const staffId = page.data('staff-id');
    const staffName = page.data('staff-name');
    const workloadUrl = page.data('workload-url');
    const specializationUrl = page.data('specialization-url');
    const tableUrl = page.data('table-url');

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // 1. Get filters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const startDate = urlParams.get('start_date');
    const endDate = urlParams.get('end_date');
    const areaId = urlParams.get('area_id');

    // Display filter info
    $('#filter-display-date').text(`${startDate} to ${endDate}`);

    const ajaxParams = { start_date: startDate, end_date: endDate, area_id: areaId };

    // 2. Render Workload Chart
    $.get(workloadUrl, ajaxParams, function (response) {
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
    $.get(specializationUrl, ajaxParams, function (response) {
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
            data: function (d) {
                d.start_date = startDate;
                d.end_date = endDate;
                d.area_id = areaId;
            }
        },
        scrollX: true,
        columns: [
            { data: 'tanggal',         name: 'order_sessions.tanggal', width: '80px' },
            { data: 'customer_name',   name: 'customer_name',   orderable: false, width: '120px' },
            { data: 'alamat_maps',     name: 'alamat_maps',     orderable: false, width: '160px' },
            { data: 'invoice_total',   name: 'invoice_total',   orderable: false, width: '110px' },
            { data: 'staff_notes',     name: 'staff_notes',     orderable: false, width: '110px' },
            { data: 'staff_attendance',name: 'staff_attendance',orderable: false, width: '75px' },
            { data: 'mesin',           name: 'mesin',           orderable: false, width: '150px' },
            { data: 'foto',            name: 'foto',            orderable: false, width: '80px' },
            {
                data: null, orderable: false, width: '60px',
                render: function(data) {
                    return data.so_id
                        ? '<a href="/service-orders/' + data.so_id + '" target="_blank" class="btn btn-sm btn-outline-primary">SO</a>'
                        : '—';
                }
            },
            {
                data: null, orderable: false, width: '140px',
                render: function(data) {
                    if (!data.invoice_id) return '—';
                    const statusLabel = (data.invoice_status_plain || '').toLowerCase();
                    return '<a href="' + data.invoice_show_url + '" target="_blank" class="btn btn-sm btn-outline-secondary">Invoice [' + capitalizeFirst(statusLabel || '—') + ']</a>';
                }
            },
        ],
        drawCallback: function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new window.bootstrap.Tooltip(tooltipTriggerEl);
            });
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new window.bootstrap.Popover(popoverTriggerEl);
            });
        },
        pageLength: 25,
        order: [[0, 'asc']]
    });
});
