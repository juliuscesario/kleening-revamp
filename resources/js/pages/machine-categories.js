// resources/js/pages/machine-categories.js

$(function() {
    const ajaxUrl = $('#machine-categories-table').data('url');
    const apiUrl = $('#machine-categories-table').data('api-url');

    const modalElement = document.getElementById('modal-machine-category');
    const modalInstance = new bootstrap.Modal(modalElement);

    const table = $('#machine-categories-table').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: ajaxUrl,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'code_prefix', name: 'code_prefix' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'is_active', name: 'is_active' },
            { data: 'machines_count', name: 'machines_count', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#add-category-button').on('click', function() {
        $('#machine-category-form').trigger("reset");
        $('#machine-category-id').val('');
        $('#machine-category-sort-order').val('0');
        $('#machine-category-is-active').prop('checked', true);
        $('#modal-title').html("Tambah Kategori Mesin");
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        modalInstance.show();
    });

    $('body').on('click', '.editMachineCategory', function() {
        const category_id = $(this).data('id');
        $.get(`${apiUrl}/${category_id}`, function(data) {
            $('#modal-title').html("Edit Kategori Mesin");
            $('#machine-category-id').val(data.id);
            $('#machine-category-name').val(data.name);
            $('#machine-category-code-prefix').val(data.code_prefix);
            $('#machine-category-sort-order').val(data.sort_order);
            $('#machine-category-is-active').prop('checked', data.is_active);
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            modalInstance.show();
        });
    });

    $('#machine-category-form').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const formData = $(this).serialize();
        const category_id = $('#machine-category-id').val();
        const url = category_id ? `${apiUrl}/${category_id}` : apiUrl;
        const method = category_id ? 'PUT' : 'POST';

        // Ensure is_active is sent as boolean
        formData.is_active = $('#machine-category-is-active').is(':checked') ? '1' : '0';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function() {
                modalInstance.hide();
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data kategori mesin berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function(jqXHR) {
                if (jqXHR.status === 403) {
                    modalInstance.hide();
                    Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk menyimpan data ini.', 'error');
                } else if (jqXHR.status === 422) {
                    const errors = jqXHR.responseJSON.errors;
                    if (errors.name) {
                        $('#machine-category-name').addClass('is-invalid');
                        $('#name-error').text(errors.name[0]);
                    }
                    if (errors.code_prefix) {
                        $('#machine-category-code-prefix').addClass('is-invalid');
                        $('#code_prefix-error').text(errors.code_prefix[0]);
                    }
                } else {
                    modalInstance.hide();
                    Swal.fire('Error!', jqXHR.responseJSON.message || 'Terjadi kesalahan di server!', 'error');
                }
            }
        });
    });

    $('body').on('click', '.deleteMachineCategory', function() {
        const category_id = $(this).data("id");
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data ini tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: `${apiUrl}/${category_id}`,
                    success: function() {
                        table.ajax.reload();
                        Swal.fire('Dihapus!', 'Data kategori mesin berhasil dihapus.', 'success');
                    },
                    error: function(jqXHR) {
                        if (jqXHR.status === 403) {
                            Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk menghapus data ini.', 'error');
                        } else if (jqXHR.status === 422) {
                            Swal.fire('Gagal!', jqXHR.responseJSON.message || 'Kategori ini masih memiliki mesin terkait.', 'error');
                        } else {
                            Swal.fire('Gagal!', jqXHR.responseJSON.message || 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    }
                });
            }
        });
    });
});
