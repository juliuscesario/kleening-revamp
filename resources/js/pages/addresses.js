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
});
