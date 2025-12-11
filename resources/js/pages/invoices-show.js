$(function() {
    $('.change-status-btn').on('click', function() {
        const invoiceId = $(this).data('id');
        const newStatus = $(this).data('new-status');

        if (confirm(`Are you sure you want to change the status to ${newStatus}?`)) {
            $.ajax({
                url: `/invoices/${invoiceId}/status`,
                method: 'PUT',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    status: newStatus,
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Status updated successfully!');
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to update status.');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred.';
                    alert(errorMsg);
                }
            });
        }
    });

    $('#savePaymentBtn').on('click', function() {
        const form = $('#markAsPaidForm');
        const invoiceId = form.find('#invoice_id').val();

        $.ajax({
            url: '/payments',
            method: 'POST',
            data: form.serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            success: function(response) {
                if (response.success) {
                    alert('Payment created successfully!');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to create payment.');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred.';
                alert(errorMsg);
            }
        });
    });
});
