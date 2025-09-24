// resources/js/pages/service-orders.js
$(function() {
    const ajaxUrl = $('#service-orders-table').data('url');
    const updateUrlTemplate = $('#service-orders-table').data('update-url-template');

    $('#service-orders-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: ajaxUrl,
        columns: [
            { data: 'so_number', name: 'so_number' },
            { data: 'customer_name', name: 'customer.name' },
            { data: 'work_date', name: 'work_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    // Handle status change button clicks
    $('#service-orders-table').on('click', '.change-status-btn', function() {
        const serviceOrderId = $(this).data('id');
        const newStatus = $(this).data('new-status');
        const currentStatus = $(this).closest('tr').find('span.badge').text().toLowerCase(); // Get current status from badge
        let ownerPassword = null;

        const updateUrl = updateUrlTemplate.replace('__SERVICE_ORDER_ID__', serviceOrderId);

        const performStatusUpdate = () => {
            const requestData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status: newStatus,
            };

            if (ownerPassword !== null) {
                requestData.owner_password = ownerPassword;
            }

            $.ajax({
                url: updateUrl,
                method: 'PUT',
                data: requestData,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Status updated successfully!');
                        $('#service-orders-table').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message || 'Failed to update status.');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred.';
                    toastr.error(errorMsg);
                }
            });
        };

        if (currentStatus === 'proses' && newStatus === 'batal') {
            // Prompt for owner password
            Swal.fire({
                title: 'Konfirmasi Pembatalan',
                html: 'Untuk membatalkan pesanan dari status \'proses\', masukkan password owner:',
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Konfirmasi',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    ownerPassword = password;
                    return true; // Proceed to AJAX call
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    performStatusUpdate();
                }
            });
        } else {
            // For other transitions, confirm directly
            Swal.fire({
                title: 'Konfirmasi Perubahan Status',
                text: `Anda yakin ingin mengubah status menjadi ${newStatus}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, ubah!'
            }).then((result) => {
                if (result.isConfirmed) {
                    performStatusUpdate();
                }
            });
        }
    });
});