// resources/js/pages/areas.js

$(function() {
    const ajaxUrl = $('#areas-table').data('url');
    const apiUrl = $('#areas-table').data('api-url');
    
    // Get modal instance using Bootstrap 5 API
    const modalElement = document.getElementById('modal-area');
    const modalInstance = new bootstrap.Modal(modalElement);

    const table = $('#areas-table').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#add-area-button').on('click', function() {
        $('#area-form').trigger("reset");
        $('#area-id').val('');
        $('#modal-title').html("Tambah Area Baru");
        $('.form-control').removeClass('is-invalid');
        modalInstance.show(); // Use Bootstrap 5 API
    });

    $('body').on('click', '.editArea', function() {
        const area_id = $(this).data('id');
        $.get(`${apiUrl}/${area_id}`, function(data) {
            $('#modal-title').html("Edit Area");
            $('#area-id').val(data.data.id);
            $('#area-name').val(data.data.name);
            $('.form-control').removeClass('is-invalid');
            modalInstance.show(); // Use Bootstrap 5 API
        });
    });

    $('#area-form').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const formData = $(this).serialize();
        const area_id = $('#area-id').val();
        const url = area_id ? `${apiUrl}/${area_id}` : apiUrl;
        const method = area_id ? 'PUT' : 'POST';
        $.ajax({
            url: url, 
            type: method, 
            data: formData,
            success: function() {
                modalInstance.hide(); // Use Bootstrap 5 API
                table.ajax.reload();
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Berhasil!', 
                    text: 'Data area berhasil disimpan.', 
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
                        $('#area-name').addClass('is-invalid');
                        $('#name-error').text(errors.name[0]);
                    }
                } else {
                    modalInstance.hide();
                    Swal.fire('Error!', jqXHR.responseJSON.message || 'Terjadi kesalahan di server!', 'error');
                }
            }
        });
    });

    $('body').on('click', '.deleteArea', function() {
        const area_id = $(this).data("id");
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
                    url: `${apiUrl}/${area_id}`,
                    success: function() {
                        table.ajax.reload();
                        Swal.fire('Dihapus!', 'Data area berhasil dihapus.', 'success');
                    },
                    error: function(jqXHR) {
                        if (jqXHR.status === 403) {
                            Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk menghapus data ini.', 'error');
                        } else {
                            Swal.fire('Gagal!', jqXHR.responseJSON.message || 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    }
                });
            }
        });
    });
});