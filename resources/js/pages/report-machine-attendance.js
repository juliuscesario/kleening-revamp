$(function() {
    const page = $('#machine-attendance-report-page');
    if (!page.length) {
        return;
    }

    const reportTable = $('#machine-attendance-report-table');
    const reportAjaxUrl = reportTable.data('url');
    const apiUrl = '/api/machine-attendances';

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
            { data: 'action', name: 'action', orderable: false, searchable: false }
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

    // --- View attendance detail ---
    $(document).on('click', '.viewAttendance', function() {
        const id = $(this).data('id');
        $.get(`${apiUrl}/${id}`, function(data) {
            $('#view-staff-name').text(data.staff_name);
            $('#view-date').text(data.date);
            $('#view-machines').text(data.machines);

            if (data.status === 'closed') {
                $('#view-status').html('<span class="badge bg-green-lt">Closed</span>');
            } else {
                $('#view-status').html('<span class="badge bg-red-lt">Open</span>');
            }

            // Photo pergi
            if (data.photo_pergi) {
                $('#view-photo-pergi').html(
                    '<a href="#" class="view-photo-link" data-src="' + data.photo_pergi + '">' +
                    '<img src="' + data.photo_pergi + '" alt="Foto Pergi" style="max-width: 150px; max-height: 150px; object-fit: cover; cursor: pointer; border-radius: 4px;">' +
                    '</a>'
                );
            } else {
                $('#view-photo-pergi').html('<span class="text-muted">—</span>');
            }
            $('#view-photo-pergi-at').text(data.photo_pergi_at || '—');

            // Photo pulang
            if (data.photo_pulang) {
                $('#view-photo-pulang').html(
                    '<a href="#" class="view-photo-link" data-src="' + data.photo_pulang + '">' +
                    '<img src="' + data.photo_pulang + '" alt="Foto Pulang" style="max-width: 150px; max-height: 150px; object-fit: cover; cursor: pointer; border-radius: 4px;">' +
                    '</a>'
                );
            } else {
                $('#view-photo-pulang').html('<span class="text-muted">Belum upload</span>');
            }
            $('#view-photo-pulang-at').text(data.photo_pulang_at || '—');

            // Catatan
            $('#view-catatan').text(data.catatan || '—');

            // Force close button: only show when status is open (photo_pulang_at is null)
            if (!data.photo_pulang_at) {
                $('#btn-force-close').removeClass('d-none').data('id', data.id);
            } else {
                $('#btn-force-close').addClass('d-none');
            }

            $('#modal-view-attendance').modal('show');
        });
    });

    // --- View full photo (from detail modal) ---
    $(document).on('click', '.view-photo-link', function(e) {
        e.preventDefault();
        const src = $(this).data('src');
        $('#photo-full-image').attr('src', src);
        $('#modal-photo-full').modal('show');
    });

    // --- Force close ---
    $(document).on('click', '.forceCloseAttendance', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Force Close Attendance?',
            text: 'Mesin akan menjadi available kembali. Attendance ditutup tanpa foto pulang.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Force Close',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e53e3e',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/${id}/force-close`,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        dataTable.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal', 'error');
                    }
                });
            }
        });
    });

    // --- Edit catatan ---
    $(document).on('click', '.editAttendance', function() {
        const id = $(this).data('id');
        $.get(`${apiUrl}/${id}`, function(data) {
            $('#edit-attendance-id').val(id);
            $('#edit-catatan').val(data.catatan || '');
            $('#modal-edit-catatan').modal('show');
        });
    });

    $('#btnsave-catatan').on('click', function() {
        const id = $('#edit-attendance-id').val();
        $.ajax({
            url: `${apiUrl}/${id}`,
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { catatan: $('#edit-catatan').val() },
            success: function(res) {
                $('#modal-edit-catatan').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                dataTable.ajax.reload();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Gagal', 'error');
            }
        });
    });

    // --- Delete ---
    $(document).on('click', '.deleteAttendance', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Attendance?',
            text: 'Data dan foto akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e53e3e',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/${id}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        dataTable.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal', 'error');
                    }
                });
            }
        });
    });
});
