import './bootstrap';

// 1. Import jQuery first and make it globally available.
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// 2. Import Bootstrap's complete JavaScript bundle.
// This will power ALL components like modals, dropdowns, etc.
// 2. Explicitly import and initialize Bootstrap JS.
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// NOTE: We are no longer importing '@tabler/core/dist/js/tabler.min.js'

// 3. Import other plugins like DataTables.
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

// 4. Import and set up SweetAlert2.
import Swal from 'sweetalert2';
window.Swal = Swal;

// --- Global AJAX Setup and Error Handling ---
const token = localStorage.getItem('auth_token');
if (token) {
    $.ajaxSetup({
        headers: { 'Authorization': 'Bearer ' + token }
    });
}

$(document).ajaxError(function(event, jqxhr) {
    if (jqxhr.status === 401) {
        if (!Swal.isVisible()) {
            Swal.fire({
                title: 'Sesi Habis',
                text: 'Sesi Anda telah berakhir. Silakan login kembali.',
                icon: 'warning',
                confirmButtonText: 'Login',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                window.location.href = '/login';
            });
        }
    }
});
// --- End of Global Setup ---

// 5. Conditionally load page-specific scripts after the DOM is ready.
$(function() {
    if (document.getElementById('areas-table')) {
        import('./pages/areas.js');
    }
    if (document.getElementById('service-categories-table')) {
        import('./pages/service-categories.js');
    }
    if (document.getElementById('staff-table')) {
        import('./pages/staff.js');
    }
    if (document.getElementById('services-table')) {
        import('./pages/services.js');
    }
    if (document.getElementById('customers-table')) {
        import('./pages/customers.js');
    }
    if (document.getElementById('addresses-table')) {
        import('./pages/addresses.js');
    }
    if (document.getElementById('customer-detail-page')) {
        import('./pages/customer-detail.js');
    }
    if (document.getElementById('service-orders-table')) {
        import('./pages/service-orders.js');
    }
});