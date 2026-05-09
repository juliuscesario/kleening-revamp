$(function() {
    const ajaxUrl = $('#machine-attendances-table').data('url');
    const apiUrl = $('#machine-attendances-table').data('api-url');

    // Set default date filters to today
    const today = new Date().toISOString().split('T')[0];
    $('#filter-date-from').val(today);
    $('#filter-date-to').val(today);

    const table = $('#machine-attendances-table').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: ajaxUrl,
            data: function(d) {
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
                d.staff_id = $('#filter-staff').val();
                d.area_id = $('#filter-area').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'date', name: 'date' },
            { data: 'staff_name', name: 'staff.name' },
            { data: 'machines', name: 'machines', orderable: false },
            { data: 'photo_pergi_at', name: 'photo_pergi_at', orderable: false },
            { data: 'photo_pulang_at', name: 'photo_pulang_at', orderable: false },
            { data: 'catatan', name: 'catatan', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Filter handlers
    $('#filter-date-from, #filter-date-to, #filter-staff, #filter-area, #filter-status').on('change', function() {
        table.ajax.reload();
    });

    // View attendance
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

    // View full photo
    $(document).on('click', '.view-photo-link', function(e) {
        e.preventDefault();
        const src = $(this).data('src');
        $('#photo-full-image').attr('src', src);
        $('#modal-photo-full').modal('show');
    });

    // Force close
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
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal', 'error');
                    }
                });
            }
        });
    });

    // Edit catatan
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
                table.ajax.reload();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Gagal', 'error');
            }
        });
    });

    // Delete
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
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Gagal', 'error');
                    }
                });
            }
        });
    });
});
