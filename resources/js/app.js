// resources/js/app.js

import './bootstrap';

// 1. Import jQuery first and make it globally available.
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

// 2. Import Tabler's main JavaScript AFTER jQuery.
import '@tabler/core/dist/js/tabler.min.js';

// 3. Import any other plugins that depend on jQuery, like DataTables.
import 'datatables.net-bs5';

// 4. Conditionally load the page-specific script.
if (document.getElementById('areas-table')) {
    import('./pages/areas.js');
}