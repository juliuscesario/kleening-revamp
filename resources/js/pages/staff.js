$(function () {
    const apiUrl = $('#staff-table').data('api-url');
    const staffModal = new bootstrap.Modal(document.getElementById('modal-staff'));
    let staffTable;

    // Function to reset form and modal
    function resetForm() {
        $('#staff-form')[0].reset();
        $('#staff-id').val('');
        $('#staff-form .is-invalid').removeClass('is-invalid');
        $('#staff-form .invalid-feedback').text('');
        $('#modal-title').text('Tambah Staff Baru');
        $('#staff-password').attr('placeholder', 'Password');
        $('#staff-password').parent().find('.form-hint').hide();
    }

    // Initialize DataTable
    staffTable = $('#staff-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: $('#staff-table').data('url'),
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'phone_number', name: 'phone_number' },
            { data: 'area', name: 'area.name' },
            { data: 'role', name: 'user.role' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Show modal for adding a new staff member
    $('#add-staff-button').on('click', function () {
        resetForm();
        staffModal.show();
    });

    // Handle form submission (Create/Update)
    $('#staff-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#staff-id').val();
        const url = id ? `${apiUrl}/${id}` : apiUrl;
        const method = id ? 'PUT' : 'POST';

        let formData = {
            name: $('#staff-name').val(),
            phone_number: $('#staff-phone_number').val(),
            area_id: $('#staff-area_id').val(),
            role: $('#staff-role').val(),
        };

        // Only include password if it's not empty
        const password = $('#staff-password').val();
        if (password) {
            formData.password = password;
        }
        
        // For PUT method, Laravel expects _method
        if(method === 'PUT') {
            formData._method = 'PUT';
        }

        $.ajax({
            url: url,
            method: 'POST', // Using POST for PUT as well with _method
            data: formData,
            success: function (response) {
                staffModal.hide();
                Swal.fire('Sukses!', response.message, 'success');
                staffTable.ajax.reload();
            },
            error: function (jqXHR) {
                if (jqXHR.status === 403) {
                    staffModal.hide();
                    Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk menyimpan data ini.', 'error');
                } else if (jqXHR.status === 422) {
                    const errors = jqXHR.responseJSON.errors;
                    $('#staff-form .is-invalid').removeClass('is-invalid');
                    $('#staff-form .invalid-feedback').text('');
                    if (errors) {
                        Object.keys(errors).forEach(function (key) {
                            let field = key.replace('.', '_');
                            $(`#staff-${field}`).addClass('is-invalid');
                            $(`#${field}-error`).text(errors[key][0]);
                        });
                    }
                } else {
                    staffModal.hide();
                    Swal.fire('Error!', jqXHR.responseJSON.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
                }
            }
        });
    });

    // Handle edit button click
    $('#staff-table').on('click', '.edit-button', function () {
        const id = $(this).data('id');
        $.get(`${apiUrl}/${id}`, function (data) {
            resetForm();
            $('#modal-title').text('Edit Staff');
            $('#staff-id').val(data.data.id);
            $('#staff-name').val(data.data.name);
            $('#staff-phone_number').val(data.data.phone_number);
            $('#staff-area_id').val(data.data.area.id);
            if (data.data.user_account) {
                $('#staff-role').val(data.data.user_account.role);
            }
            $('#staff-password').attr('placeholder', 'Kosongkan jika tidak ingin mengubah');
            $('#staff-password').parent().find('.form-hint').show();
            staffModal.show();
        });
    });

    // Handle resign button click
    $('#staff-table').on('click', '.resign-button', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Anda yakin?',
            text: "Akses login staff akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, resign!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/${id}/resign`,
                    method: 'POST',
                    success: function (response) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        staffTable.ajax.reload();
                    },
                    error: function (jqXHR) {
                        if (jqXHR.status === 403) {
                            Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk melakukan aksi ini.', 'error');
                        } else {
                            Swal.fire('Error!', jqXHR.responseJSON.message || 'Gagal memproses permintaan.', 'error');
                        }
                    }
                });
            }
        });
    });
});