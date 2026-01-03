// resources/js/pages/customers.js

$(function () {
    const ajaxUrl = $('#customers-table').data('url');
    const apiUrl = $('#customers-table').data('api-url');

    const modalElement = document.getElementById('modal-customer');
    const modalInstance = new bootstrap.Modal(modalElement);
    const addressesModalElement = document.getElementById('modal-show-addresses');
    const addressesModalInstance = new bootstrap.Modal(addressesModalElement);

    const table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: ajaxUrl,
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'phone_number', name: 'phone_number' },
            { data: 'addresses_count', name: 'addresses_count', searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'last_order_date', name: 'last_order_date', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Show/hide address fields based on checkbox
    $('#add-address-checkbox').on('change', function () {
        if ($(this).is(':checked')) {
            $('#address-fields').slideDown();
        } else {
            $('#address-fields').slideUp();
        }
    });

    function resetForm() {
        $('#customer-form').trigger("reset");
        $('#customer-id').val('');
        $('#add-address-checkbox').prop('checked', false);
        $('#copy-customer-data').prop('checked', false); // Reset copy checkbox
        $('#address-fields').hide();
        $('#modal-title').html("Tambah Customer Baru");
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        // For edit, we disable adding address
        $('#add-address-checkbox').closest('.form-check').show();
    }

    $('#add-customer-button').on('click', function () {
        resetForm();
        modalInstance.show();
    });

    // Auto-copy customer data logic
    $('#copy-customer-data').on('change', function () {
        if ($(this).is(':checked')) {
            $('#address-contact_name').val($('#customer-name').val());
            $('#address-contact_phone').val($('#customer-phone_number').val());

            // Make them readonly to indicate they are synced (optional, based on preference)
            // $('#address-contact_name').prop('readonly', true);
            // $('#address-contact_phone').prop('readonly', true);
        } else {
            $('#address-contact_name').val('');
            $('#address-contact_phone').val('');

            // $('#address-contact_name').prop('readonly', false);
            // $('#address-contact_phone').prop('readonly', false);
        }
    });

    // Real-time sync
    $('#customer-name').on('input', function () {
        if ($('#copy-customer-data').is(':checked')) {
            $('#address-contact_name').val($(this).val());
        }
    });

    $('#customer-phone_number').on('input', function () {
        if ($('#copy-customer-data').is(':checked')) {
            $('#address-contact_phone').val($(this).val());
        }
    });

    // Handle Show Addresses button
    $('body').on('click', '.show-addresses', function () {
        const customer_id = $(this).data('id');
        $.get(`${apiUrl}/${customer_id}/addresses`, function (response) {
            const addresses = response.data;
            let content = '<p>Customer ini belum memiliki alamat.</p>';
            if (addresses && addresses.length > 0) {
                content = '<ul class="list-group list-group-flush">';
                addresses.forEach(function (address) {
                    let mapLink = '';
                    if (address.google_maps_link) {
                        mapLink = `<a href="${address.google_maps_link}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-ghost-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-pin" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>
                        </a>`;
                    }
                    content += `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${address.label}</strong> (${address.contact_name} - ${address.contact_phone})<br>
                            <span class="text-muted">${address.full_address}</span>
                        </div>
                        <div>${mapLink}</div>
                    </li>`;
                });
                content += '</ul>';
            }
            $('#address-list-container').html(content);
            addressesModalInstance.show();
        });
    });

    $('body').on('click', '.edit-customer', function () {
        const customer_id = $(this).data('id');
        $.get(`${apiUrl}/${customer_id}`, function (data) {
            resetForm();
            $('#modal-title').html("Edit Customer");
            $('#customer-id').val(data.data.id);
            $('#customer-name').val(data.data.name);
            $('#customer-phone_number').val(data.data.phone_number);

            // Hide address section when editing customer
            $('#add-address-checkbox').closest('.form-check').hide();
            $('#address-fields').hide();

            modalInstance.show();
        });
    });

    $('#customer-form').on('submit', function (e) {
        e.preventDefault();
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        const formData = $(this).serialize();
        const customer_id = $('#customer-id').val();
        const url = customer_id ? `${apiUrl}/${customer_id}` : apiUrl;
        const method = customer_id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function () {
                modalInstance.hide();
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data customer berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function (jqXHR) {
                if (jqXHR.status === 403) {
                    modalInstance.hide();
                    Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak memiliki izin untuk menyimpan data ini.', 'error');
                } else if (jqXHR.status === 422) {
                    const errors = jqXHR.responseJSON.errors;
                    Object.keys(errors).forEach(function (key) {
                        $(`#${key}-error`).text(errors[key][0]).siblings('.form-control').addClass('is-invalid');
                    });
                } else {
                    modalInstance.hide();
                    Swal.fire('Error!', jqXHR.responseJSON.message || 'Terjadi kesalahan di server!', 'error');
                }
            }
        });
    });

    $('body').on('click', '.delete-customer', function () {
        const customer_id = $(this).data("id");
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data customer ini akan dihapus!",
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
                    url: `${apiUrl}/${customer_id}`,
                    success: function () {
                        table.ajax.reload();
                        Swal.fire('Dihapus!', 'Data customer berhasil dihapus.', 'success');
                    },
                    error: function (jqXHR) {
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
