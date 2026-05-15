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

    // ── Periode shortcut buttons ─────────────────────────────────────────
    const periodeDescriptions = {
        1: 'Tgl kerja: 1–10 | Submit: tgl 11 | Bayar: tgl 12',
        2: 'Tgl kerja: 11–20 | Submit: tgl 21 | Bayar: tgl 22',
        3: 'Tgl kerja: 21–akhir bulan | Submit: tgl 1 bulan depan | Bayar: tgl 2 bulan depan',
    };

    // Pre-select current month and year
    $('#shortcut-month').val(new Date().getMonth());
    $('#shortcut-year').val(new Date().getFullYear());

    function getPeriodeDates(periode) {
        const month   = parseInt($('#shortcut-month').val()); // 0-indexed
        const year    = parseInt($('#shortcut-year').val());
        const lastDay = new Date(year, month + 1, 0).getDate();
        const pad     = (n) => String(n).padStart(2, '0');
        const fmt     = (y, m, d) => `${y}-${pad(m + 1)}-${pad(d)}`;

        if (periode === 1) {
            return { start: fmt(year, month, 1),  end: fmt(year, month, 10) };
        } else if (periode === 2) {
            return { start: fmt(year, month, 11), end: fmt(year, month, 20) };
        } else {
            return { start: fmt(year, month, 21), end: fmt(year, month, lastDay) };
        }
    }

    $(document).on('click', '.periode-btn', function () {
        const periode = parseInt($(this).data('periode'));
        const dates   = getPeriodeDates(periode);

        $('#filter-start-date').val(dates.start);
        $('#filter-end-date').val(dates.end);
        $('#periode-desc').text(periodeDescriptions[periode]);

        $('.periode-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        $(this).removeClass('btn-outline-secondary').addClass('btn-primary');

        $('#apply-filters').trigger('click');
    });

    $('#filter-start-date, #filter-end-date').on('change', function () {
        $('.periode-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
        $('#periode-desc').text('');
    });
    // ── End periode shortcuts ────────────────────────────────────────────

    $('#apply-filters').on('click', function () {
        dataTable.ajax.reload();
    });
});
