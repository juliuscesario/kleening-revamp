import DataTable from 'datatables.net-bs5';

$(function() {
    const table = new DataTable('#invoices-table', {
        ajax: {
            url: '/data/invoices',
            dataSrc: function (json) {
                console.log(json);
                return json.data;
            }
        },
        processing: true,
        serverSide: true,
        responsive: true,
        columns: [
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'so_number', name: 'serviceOrder.so_number' },
            { data: 'customer_name', name: 'customer_name', orderable: false, searchable: false },
            { data: 'customer_phone', name: 'customer_phone', orderable: false, searchable: false },
            { data: 'issue_date', name: 'issue_date' },
            { data: 'due_date', name: 'due_date' },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    $('#invoices-table').on('click', '.change-status-btn', function() {
        const invoiceId = $(this).data('id');
        const newStatus = $(this).data('new-status');

        if (newStatus === 'paid') {
            $('#invoice_id').val(invoiceId);
            const modal = new bootstrap.Modal(document.getElementById('markAsPaidModal'));
            modal.show();
        } else {
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
                            table.ajax.reload();
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
                    const modal = bootstrap.Modal.getInstance(document.getElementById('markAsPaidModal'));
                    modal.hide();
                    table.ajax.reload();
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
