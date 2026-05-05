<!-- Full version: .context/archive/conventions-2026-05-04.md -->

# Kleening.id — Coding Conventions

## General
Laravel + Blade (NOT SPA), mobile-first, fat controller pattern. Areas: Jabodetabek / Serang / Malang.

## Naming

| Type | Pattern | Example |
|------|---------|---------|
| Models | Singular PascalCase | `ServiceOrder` |
| Controllers | `{Web\|Api\|Auth}/{Name}Controller.php` | `Web/InvoiceController.php` |
| Web Routes | `web.{resource}.{action}` | `web.service-orders.index` |
| API Routes | RESTful `apiResource` | `apiResource('areas')` |
| Views | `pages/{resource}/{action}.blade.php` | `pages/staff/index.blade.php` |
| Partials | `_name.blade.php` | `_edit_modal.blade.php` |
| Migrations | `YYYY_MM_DD_HHMMSS_desc.php` | `2025_01_01_create_areas_table` |

## File Organization
Controllers → `app/Http/Controllers/{Web|Api|Auth}/` · Models → `app/Models/` · Policies → `app/Policies/` · Resources → `app/Http/Resources/` · Events → `app/Events/` · Notifications → `app/Notifications/` · Views → `resources/views/pages/{feature}/`

## Code Style
4-space indent · LF endings · UTF-8 · PSR-12 (Laravel Pint) · trailing whitespace trimmed · final newline required · type hints on all PHP signatures

## Routing
```php
Route::resource('areas', AreaController::class)->names('web.areas');
Route::middleware(['auth', 'role:owner,co_owner'])->group(fn() => ...);
Route::apiResource('areas', AreaController::class);
```

## AJAX / Frontend
- `fetch()` only (not axios), CSRF from `<meta name="csrf-token">`
- `@push('styles')` / `@push('scripts')` for assets
- No `<form>` for inline edits — fetch + handlers
- Libraries: SweetAlert2, Toastr, Select2, ApexCharts, DataTables

## Model Conventions
- **AreaScope** on Address, Customer, ServiceOrder, Staff, Invoice — auto-filters by `co_owner.area_id`. Bypass: `Model::withoutGlobalScopes()`
- **Soft deletes**: Customer, Address — use `->withTrashed()`
- **Field notes**: `work_time` = `H:i:s` (use `work_time_formatted` accessor) · `quantity` = decimal · pivot `service_order_staff.signature_image`

### Status Constants
| ServiceOrder | Invoice |
|--------------|---------|
| `STATUS_BOOKED` `STATUS_PROSES` `STATUS_DONE` `STATUS_INVOICED` `STATUS_CANCELLED` | `STATUS_NEW` `STATUS_SENT` `STATUS_OVERDUE` `STATUS_PAID` `STATUS_CANCELLED` |

## API
Sanctum auth · always use API Resources · login = owner only · Scribe docs at `/docs` (`php artisan scribe:generate`)

## Service Categories
HV = Hydrovacuum · CC = Premium Wash · GC = General Cleaning · DC = Deep Cleaning · CID = Car Interior Detailing · Poles · Survey

## Other
- **Notifications**: DB channel, 4 types (InvoiceOverdue, InvoicePaid, ServiceOrderDone, ServiceOrderInvoiced)
- **PDF**: DOMPDF — templates at `resources/views/pdf/`
- **Settings**: `AppSetting::get('key')` / `AppSetting::set('key', 'value')` (cached)
- **Blade components** (13 Breeze): `application-logo`, `danger-button`, `dropdown`, `modal`, `primary-button`, `text-input`, etc.

## Checklist
- [ ] `fetch()` not axios · [ ] `@push` for assets · [ ] No `<form>` inline edits · [ ] AreaScope aware · [ ] API Resources for JSON · [ ] `canTransitionTo()` for status · [ ] CSRF in headers · [ ] Mobile-first · [ ] 4-space + PSR-12 · [ ] Type hints
