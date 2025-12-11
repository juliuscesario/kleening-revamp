$(function() {
    function formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
    }

    function calculateSubtotal() {
        let subtotal = 0;
        $('.item-total').each(function() {
            subtotal += parseFloat($(this).data('total'));
        });
        console.log('Subtotal:', subtotal);
        $('#subtotal').val(subtotal.toFixed(2));
        $('#subtotal_display').val(formatCurrency(subtotal));
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let subtotal = parseFloat($('#subtotal').val()) || 0;
        let discount = parseFloat($('#discount').val()) || 0;
        let discountType = $('#discount_type').val();
        let transportFee = parseFloat($('#transport_fee').val()) || 0;

        let discountAmount = 0;
        if (discountType === 'percentage') {
            discountAmount = (subtotal * discount) / 100;
        } else {
            discountAmount = discount;
        }

        let grandTotal = (subtotal - discountAmount) + transportFee;
        console.log('Grand Total:', grandTotal);
        $('#grand_total').val(grandTotal.toFixed(2));
        $('#grand_total_display').val(formatCurrency(grandTotal));
    }

    function setDueDate() {
        let issueDate = new Date($('#issue_date').val());
        let days = $(this).data('days');
        if (days !== undefined) {
            issueDate.setDate(issueDate.getDate() + days);
            let year = issueDate.getFullYear();
            let month = ('0' + (issueDate.getMonth() + 1)).slice(-2);
            let day = ('0' + issueDate.getDate()).slice(-2);
            $('#due_date').val(`${year}-${month}-${day}`);
        }
    }

    // Initial calculations
    calculateSubtotal();

    // Event listeners
    $('#discount, #discount_type, #transport_fee').on('input change', function() {
        calculateGrandTotal();
    });

    $('.due-date-btn').on('click', setDueDate);

    $('#issue_date').on('change', function(){
        // find active button and click it
        $('.due-date-btn.active').trigger('click');
    });

    // Set active button
    $('.due-date-btn').on('click', function(){
        $('.due-date-btn').removeClass('active');
        $(this).addClass('active');
    });
});