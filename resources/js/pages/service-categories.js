// resources/js/pages/service-categories.js

$(function() {
    const ajaxUrl = $('#service-categories-table').data('url');
    const apiUrl = $('#service-categories-table').data('api-url');
    
    // Get modal instance using Bootstrap 5 API
    const modalElement = document.getElementById('modal-service-category');
    const modalInstance = new bootstrap.Modal(modalElement);

    const table = $('#service-categories-table').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'category_name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#add-service-category-button').on('click', function() {
        $('#service-category-form').trigger("reset");
        $('#service-category-id').val('');
        $('#modal-title').html("Tambah Kategori Layanan Baru");
        $('.form-control').removeClass('is-invalid');
        modalInstance.show(); // Use Bootstrap 5 API
    });

    $('body').on('click', '.editServiceCategory', function() {
        const service_category_id = $(this).data('id');
        $.get(`${apiUrl}/${service_category_id}`, function(data) {
            $('#modal-title').html("Edit Kategori Layanan");
            $('#service-category-id').val(data.data.id);
            $('#service-category-name').val(data.data.name);
            $('.form-control').removeClass('is-invalid');
            modalInstance.show(); // Use Bootstrap 5 API
        });
    });

    $('#service-category-form').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const formData = $(this).serialize();
        const service_category_id = $('#service-category-id').val();
        const url = service_category_id ? `${apiUrl}/${service_category_id}` : apiUrl;
        const method = service_category_id ? 'PUT' : 'POST';
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
                    text: 'Data kategori layanan berhasil disimpan.',
                    showConfirmButton: false, 
                    timer: 1500 
                });
            },
            error: function(jqXHR) {
                if (jqXHR.status === 422) {
                    const errors = jqXHR.responseJSON.errors;
                    if (errors.category_name) {
                        $('#service-category-name').addClass('is-invalid');
                        $('#name-error').text(errors.category_name[0]);
                    }
                } else {
                    Swal.fire('Oops...', 'Terjadi kesalahan di server!', 'error');
                }
            }
        });
    });

    // $('body').on('click', '.deleteServiceCategory', function() {
    //     const service_category_id = $(this).data("id");
    //     Swal.fire({
    //         title: 'Apakah Anda Yakin?', 
    //         text: "Data ini tidak dapat dikembalikan!", 
    //         icon: 'warning',
    //         showCancelButton: true, 
    //         confirmButtonColor: '#d33', 
    //         cancelButtonColor: '#6c757d',
    //         confirmButtonText: 'Ya, hapus!', 
    //         cancelButtonText: 'Batal'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.ajax({
    //                 type: "DELETE", 
    //                 url: `${apiUrl}/${service_category_id}`,
    //                 success: function() {
    //                     table.ajax.reload();
    //                     Swal.fire('Dihapus!', 'Data kategori layanan berhasil dihapus.', 'success');
    //                 },
    //                 error: function() {
    //                     Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
    //                 }
    //             });
    //         }
    //     });
    // });
});