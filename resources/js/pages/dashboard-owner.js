import ApexCharts from 'apexcharts';

document.addEventListener("DOMContentLoaded", function () {
    const chartEl = document.getElementById('chart-daily-revenue');
    if (!chartEl) {
        return;
    }

    const dates = JSON.parse(chartEl.dataset.dates);
    const totals = JSON.parse(chartEl.dataset.totals);

    const options = {
        chart: {
            type: 'bar',
            height: 350,
            parentHeightOffset: 0,
            toolbar: { show: false },
        },
        series: [{
            name: 'Pendapatan',
            data: totals
        }],
        xaxis: {
            categories: dates,
            labels: {
                padding: 0,
            },
            tooltip: {
                enabled: false
            },
            axisBorder: {
                show: false,
            },
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: false,
            }
        },
        dataLabels: { enabled: false },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "Rp " + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            }
        }
    };

    const chart = new ApexCharts(chartEl, options);
    chart.render();
});
