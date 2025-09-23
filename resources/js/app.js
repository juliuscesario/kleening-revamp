// resources/js/app.js

import './bootstrap';

// 1. Import jQuery first and make it globally available.
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// 2. Import Bootstrap's JavaScript (required for modal, dropdown, etc.)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// 3. Import Tabler's main JavaScript AFTER jQuery and Bootstrap.
import '@tabler/core/dist/js/tabler.min.js';

// 4. Import any other plugins that depend on jQuery, like DataTables.
import 'datatables.net-bs5';

// Import SweetAlert2 and make it globally available
import Swal from 'sweetalert2';
window.Swal = Swal;

// --- Authentication Token Setup ---
const token = localStorage.getItem('auth_token');
if (token) {
    $.ajaxSetup({
        headers: {
            'Authorization': 'Bearer ' + token
        }
    });
}

// --- Global AJAX Error Handler (NEW CODE) ---
// This function will run if ANY ajax request fails
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    // If the error is a 401 Unauthorized
    if (jqxhr.status == 401) {
        // Prevent multiple pop-ups
        if (!Swal.isVisible()) {
            Swal.fire({
                title: 'Sesi Habis',
                text: 'Sesi Anda telah berakhir. Silakan login kembali untuk melanjutkan.',
                icon: 'warning',
                confirmButtonText: 'Login',
                allowOutsideClick: false, // User must interact
                allowEscapeKey: false    // User must interact
            }).then((result) => {
                // If the user clicks the "Login" button
                if (result.isConfirmed) {
                    // Redirect them to the login page
                    window.location.href = '/login';
                }
            });
        }
    }
});

// 5. Conditionally load the page-specific script.
if (document.getElementById('areas-table')) {
    import('./pages/areas.js');
}