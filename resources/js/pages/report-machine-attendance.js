$(function() {
    const page = $('#machine-attendance-report-page');
    if (!page.length) {
        return;
    }

    const reportTable = $('#machine-attendance-report-table');
    const reportAjaxUrl = reportTable.data('url');

    // Set default dates (last 10 days)
    const today = new Date();
    const tenDaysAgo = new Date(today);
    tenDaysAgo.setDate(today.getDate() - 10);
    $('#filter-date-from').val(tenDaysAgo.toISOString().split('T')[0]);
    $('#filter-date-to').val(today.toISOString().split('T')[0]);

    // Counter for stats
    let stats = { total: 0, open: 0, warnings: 0 };

    // --- DataTable ---
    const dataTable = reportTable.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: reportAjaxUrl,
            data: function(d) {
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
                d.area_id = $('#filter-area').val();
                d.staff_id = $('#filter-staff').val();
                d.category_id = $('#filter-category').val();
                d.status = $('#filter-status').val();
            },
            dataSrc: function(json) {
                // Update stats from server response
                if (json.recordsTotal !== undefined) {
                    stats.total = json.recordsTotal;
                }
                // Count open and warnings from returned data
                let openCount = 0;
                let warningCount = 0;
                if (json.data && json.data.length) {
                    json.data.forEach(function(row) {
                        if (row.status && row.status.includes('bg-red-lt')) {
                            openCount++;
                        }
                        if (row.warning && row.warning !== '—' && row.warning.trim() !== '') {
                            warningCount++;
                        }
                    });
                }
                stats.open = openCount;
                stats.warnings = warningCount;
                return json.data;
            }
        },
        columns: [
            { data: 'date', name: 'date' },
            { data: 'staff_name', name: 'staff.name' },
            { data: 'area', name: 'staff.area.name' },
            { data: 'machines', name: 'machines.code', orderable: false },
            { data: 'categories', name: 'categories.name', orderable: false },
            { data: 'jam_pergi', name: 'photo_pergi_at', orderable: false },
            { data: 'jam_pulang', name: 'photo_pulang_at', orderable: false },
            { data: 'durasi', name: 'photo_pulang_at', orderable: false },
            { data: 'catatan', name: 'catatan', orderable: false },
            { data: 'status', name: 'photo_pulang_at' },
            { data: 'warning', orderable: false },
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        drawCallback: function() {
            // Update stats on each draw
            $('#stat-total').text(stats.total);
            $('#stat-open').text(stats.open);
            $('#stat-warnings').text(stats.warnings);
        }
    });

    // --- Filter change handlers ---
    $('#filter-date-from, #filter-date-to, #filter-area, #filter-staff, #filter-category, #filter-status')
        .on('change', function() {
            dataTable.ajax.reload();
        });

    // --- Photo thumbnail click → full view modal ---
    $(document).on('click', '.photo-thumb', function(e) {
        e.preventDefault();
        const fullUrl = $(this).data('full');
        $('#photo-full-view').attr('src', fullUrl);
        new bootstrap.Modal(document.getElementById('modal-photo-view')).show();
    });
});
