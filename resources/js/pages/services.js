// resources/js/pages/services.js

$(function() {
    const ajaxUrl = $('#services-table').data('url');
    const apiUrl = $('#services-table').data('api-url');
    
    const modalElement = document.getElementById('modal-service');
    const modalInstance = new bootstrap.Modal(modalElement);

    const table = $('#services-table').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'category_name', name: 'category.name' },
            { data: 'price', name: 'price' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#add-service-button').on('click', function() {
        $('#service-form').trigger("reset");
        $('#service-id').val('');
        $('#modal-title').html("Tambah Layanan Baru");
        $('.form-control').removeClass('is-invalid');
        modalInstance.show();
    });

    $('body').on('click', '.edit-service', function() {
        const service_id = $(this).data('id');
        $.get(`${apiUrl}/${service_id}`, function(data) {
            $('#modal-title').html("Edit Layanan");
            $('#service-id').val(data.data.id);
            $('#service-name').val(data.data.name);
            $('#service-category_id').val(data.data.category.id);
            $('#service-price').val(data.data.price);
            $('#service-description').val(data.data.description);
            $('.form-control').removeClass('is-invalid');
            modalInstance.show();
        });
    });

    $('#service-form').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const formData = $(this).serialize();
        const service_id = $('#service-id').val();
        const url = service_id ? `${apiUrl}/${service_id}` : apiUrl;
        const method = service_id ? 'PUT' : 'POST';

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
                    text: 'Data layanan berhasil disimpan.', 
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
                    Object.keys(errors).forEach(function(key) {
                        $(`#service-${key}`).addClass('is-invalid');
                        $(`#${key}-error`).text(errors[key][0]);
                    });
                } else {
                    modalInstance.hide();
                    Swal.fire('Error!', jqXHR.responseJSON.message || 'Terjadi kesalahan di server!', 'error');
                }
            }
        });
    });

    $('body').on('click', '.delete-service', function() {
        const service_id = $(this).data("id");
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
                    url: `${apiUrl}/${service_id}`,
                    success: function() {
                        table.ajax.reload();
                        Swal.fire('Dihapus!', 'Data layanan berhasil dihapus.', 'success');
                    },
                    error: function(jqXHR) {
                        if (jqXHR.status === 403) {
                            Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk menghapus data ini.', 'error');
                        } else if (jqXHR.status === 409) {
                            Swal.fire('Gagal Menghapus!', jqXHR.responseJSON.message, 'error');
                        } else {
                            Swal.fire('Gagal!', jqXHR.responseJSON.message || 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    }
                });
            }
        });
    });
});
