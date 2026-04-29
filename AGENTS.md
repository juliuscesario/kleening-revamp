# Kleening.id — Project Context for Qwen Code

## Stack
- Laravel + Blade (NOT a SPA)
- Tabler UI framework
- jQuery (globally loaded)
- SweetAlert2 for modals/toasts
- MySQL

## Coding Rules — Always Follow
- AJAX: use fetch(), not axios
- CSS: @push('styles'), JS: @push('scripts')
- Never use <form> for inline edits — use fetch() + event handlers
- Models may have AreaScope global scope — use withoutGlobalScopes() when searching across areas
- work_time stored as H:i:s in DB
- CSRF: document.querySelector('meta[name="csrf-token"]').content
- Mobile-first — admins use phone & tablet

## Project Areas
Jabodetabek / Serang / Malang

## Service Categories
HV (Hydrovacuum), CC (Premium Wash), GC (General Cleaning),
DC (Deep Cleaning), CID (Car Interior Detailing), Poles, Survey

## Status Lifecycle
booked → proses → done → invoiced → tagih → blm bayar → lunas

## Key Directories
- Controllers: app/Http/Controllers/Web/
- Views: resources/views/pages/
- Routes: routes/web.php, routes/api.php
- Layout: resources/views/layouts/admin.blade.php



