// resources/js/pages/service-orders-show.js

$(function() {
    var d = window.soConfirmData || {};

    function toastSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }

    function fallbackCopy(text) {
        var el = document.createElement('textarea');
        el.value = text;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        try {
            document.execCommand('copy');
            toastSuccess('Copied! Paste it in WhatsApp.');
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to copy. Please copy manually.'
            });
        }
        document.body.removeChild(el);
    }

    $(document).on('click', '#btn-confirm-order', function() {
        var text =
'*BOOKING CONFIRMED*\n\n' +
'*Kontak : ' + (d.name || '') + ', ' + (d.phone || '') + '*\n' +
'Waktu : ' + (d.tanggal || '') + ', ' + (d.jam || '') + '\n' +
'Alamat : ' + (d.alamat || '') + '\n' +
'Layanan :\n' + (d.services || '') + '\n\n' +
'Reminder akan dikirimkan H-1 sebelum jadwal.\n' +
'Silakan hubungi kami jika ada pertanyaan atau kebutuhan tambahan.\n\n' +
'Thank you for trusting Kleening.id';

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                toastSuccess('Order confirmation copied! Paste it in WhatsApp.');
            }).catch(function() {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    });
});
