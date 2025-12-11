// resources/js/pages/addresses.js

$(function() {
    const ajaxUrl = $('#addresses-table').data('url');
    const apiUrl = $('#addresses-table').data('api-url');

    const table = $('#addresses-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: ajaxUrl,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'label', name: 'label' },
            { data: 'customer_name', name: 'customer.name' },
            { data: 'area_name', name: 'area.name' },
            { data: 'full_address', name: 'full_address' },
            { data: 'contact_phone', name: 'contact_phone' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('body').on('click', '.delete-address', function() {
        const address_id = $(this).data("id");
        Swal.fire({
            title: 'Apakah Anda Yakin?', 
            text: "Alamat ini akan dihapus!", 
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
                    url: `${apiUrl}/${address_id}`,
                    success: function() {
                        table.ajax.reload();
                        Swal.fire('Dihapus!', 'Alamat berhasil dihapus.', 'success');
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

    const modalElement = document.getElementById('modal-address');
    const modalInstance = new bootstrap.Modal(modalElement);

    $('body').on('click', '.edit-address', function() {
        const address_id = $(this).data('id');
        $.get(`${apiUrl}/${address_id}`, function(data) {
            $('#modal-title').html("Edit Alamat");
            $('#address-id').val(data.data.id);
            $('#address-label').val(data.data.label);
            $('#address-contact-name').val(data.data.contact_name);
            $('#address-contact-phone').val(data.data.contact_phone);
            $('#address-full-address').val(data.data.full_address);
            $('#address-google-maps-link').val(data.data.google_maps_link);
            $('.form-control').removeClass('is-invalid');
            modalInstance.show();
        });
    });

    $('#address-form').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const formData = $(this).serialize();
        const address_id = $('#address-id').val();
        const url = `${apiUrl}/${address_id}`;
        const method = 'PUT';
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
                    text: 'Data alamat berhasil disimpan.',
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
                    if (errors.label) {
                        $('#address-label').addClass('is-invalid');
                        $('#label-error').text(errors.label[0]);
                    }
                    if (errors.contact_name) {
                        $('#address-contact-name').addClass('is-invalid');
                        $('#contact-name-error').text(errors.contact_name[0]);
                    }
                    if (errors.contact_phone) {
                        $('#address-contact-phone').addClass('is-invalid');
                        $('#contact-phone-error').text(errors.contact_phone[0]);
                    }
                    if (errors.full_address) {
                        $('#address-full-address').addClass('is-invalid');
                        $('#full-address-error').text(errors.full_address[0]);
                    }
                    if (errors.google_maps_link) {
                        $('#address-google-maps-link').addClass('is-invalid');
                        $('#google-maps-link-error').text(errors.google_maps_link[0]);
                    }
                } else {
                    modalInstance.hide();
                    Swal.fire('Error!', jqXHR.responseJSON.message || 'Terjadi kesalahan di server!', 'error');
                }
            }
        });
    });
});