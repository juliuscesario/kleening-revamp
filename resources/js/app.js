import './bootstrap';
// Import JS utama dari Tabler
import '@tabler/core/dist/js/tabler.min.js';

// Import jQuery dan jadikan global
import $ from 'jquery';
window.$ = $;
window.jQuery = $;

// Import DataTables SETELAH jQuery
import 'datatables.net-bs5'; // <-- PASTIKAN BARIS INI ADA

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
