// resources/js/pages/customer-detail.js
$(function() {
    const addressesApiUrl = '/api/addresses';
    const modalElement = document.getElementById('modal-address');
    const modalInstance = new bootstrap.Modal(modalElement);

    // Handle Edit Address button click
    $('body').on('click', '.edit-address', function(e) {
        e.preventDefault();
        const addressId = $(this).data('id');
        
        $.get(`${addressesApiUrl}/${addressId}`, function(response) {
            const address = response.data;
            $('#modal-title').html("Edit Alamat");
            $('#address-id').val(address.id);
            $('#address-label').val(address.label);
            $('#address-contact-name').val(address.contact_name);
            $('#address-contact-phone').val(address.contact_phone);
            $('#address-full-address').val(address.full_address);
            $('#address-google-maps-link').val(address.google_maps_link);
            $('.form-control').removeClass('is-invalid');
            modalInstance.show();
        });
    });

    // Handle Edit Address form submission
    $('#address-form').on('submit', function(e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const addressId = $('#address-id').val();
        const formData = $(this).serialize();

        $.ajax({
            url: `${addressesApiUrl}/${addressId}`,
            type: 'PUT',
            data: formData,
            success: function() {
                modalInstance.hide();
                Swal.fire('Berhasil!', 'Alamat berhasil diperbarui.', 'success').then(() => {
                    location.reload(); // Reload page to see changes
                });
            },
            error: function(jqXHR) {
                if (jqXHR.status === 403) {
                    modalInstance.hide();
                    Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak diizinkan mengedit alamat ini.', 'error');
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

    // Handle Delete Address button click
    $('body').on('click', '.delete-address', function(e) {
        e.preventDefault();
        const addressId = $(this).data('id');

        Swal.fire({
            title: 'Anda yakin?',
            text: "Alamat ini akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${addressesApiUrl}/${addressId}`,
                    type: 'DELETE',
                    success: function() {
                        Swal.fire('Dihapus!', 'Alamat telah dihapus.', 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(jqXHR) {
                        if (jqXHR.status === 403) {
                            Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak diizinkan menghapus alamat ini.', 'error');
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus alamat.', 'error');
                        }
                    }
                });
            }
        });
    });
});