# Kleening.id — Coding Conventions

## General Rules

- **Laravel + Blade** — Server-rendered, Alpine.js for interactivity (NOT SPA)
- **Mobile-first** — Admins use phone & tablet
- **Areas**: Jabodetabek / Serang / Malang
- **Fat controller pattern** — No dedicated service/repository layer

## Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| **Models** | Singular PascalCase | `ServiceOrder`, `ServiceCategory` |
| **Web Controllers** | `Web/{Name}Controller.php` | `Web/ServiceOrderController.php` |
| **API Controllers** | `Api/{Name}Controller.php` | `Api/ServiceOrderController.php` |
| **Web Routes** | `web.{resource}.{action}` | `web.service-orders.index` |
| **Data Routes** | `data.{entity}` | `data.areas`, `data.customers` |
| **API Routes** | RESTful `apiResource` | `apiResource('areas')` |
| **Views** | `pages/{resource}/{action}.blade.php` | `pages/customers/index.blade.php` |
| **Partials** | `_partial_name.blade.php` | `_edit_modal.blade.php` |
| **Migrations** | `YYYY_MM_DD_HHMMSS_description.php` | `2025_01_01_create_areas_table` |

## File Organization

| Code Type | Location |
|-----------|----------|
| Controllers | `app/Http/Controllers/{Web\|Api\|Auth}/` |
| Models | `app/Models/` |
| Policies | `app/Policies/` |
| API Resources | `app/Http/Resources/` |
| Events/Listeners | `app/Events/`, `app/Listeners/` |
| Notifications | `app/Notifications/` |
| Services | `app/Services/` |
| Commands | `app/Console/Commands/` |
| Scopes | `app/Models/Scopes/` |
| Views | `resources/views/pages/{feature}/` |

## Code Style

- **Indent**: 4 spaces (`.editorconfig`)
- **Line endings**: LF
- **Charset**: UTF-8
- **PHP**: PSR-12 (enforced by Laravel Pint)
- **Trailing whitespace**: Trimmed on save
- **Final newline**: Required
- **Type hints**: On all PHP method signatures

## Routing

### Web Routes (`routes/web.php`)
```php
Route::resource('areas', AreaController::class)->names('web.areas');
Route::middleware(['auth', 'role:owner,co_owner'])->group(function () { ... });
```

### API Routes (`routes/api.php`)
```php
Route::apiResource('areas', AreaController::class);
Route::post('staff/{staff}/resign', [...]);
```

## AJAX / Frontend

- **Use `fetch()`, NOT axios** — CSRF from `<meta name="csrf-token">`
- **Push assets** via `@push('styles')` / `@push('scripts')`
- **No `<form>` for inline edits** — use fetch + event handlers
- **UI libraries**: SweetAlert2 (modals/toasts), Toastr, Select2, ApexCharts, DataTables

## Model Conventions

### AreaScope
Applied to: `Address`, `Customer`, `ServiceOrder`, `Staff`, `Invoice` — auto-filters by `co_owner.area_id`. Bypass: `Model::withoutGlobalScopes()`.

### Soft Deletes
`Customer` and `Address` — use `->withTrashed()` when querying deleted records.

### Status Constants
```
ServiceOrder: STATUS_BOOKED | STATUS_PROSES | STATUS_DONE | STATUS_INVOICED | STATUS_CANCELLED
Invoice:      STATUS_NEW | STATUS_SENT | STATUS_OVERDUE | STATUS_PAID | STATUS_CANCELLED
```

### Field Notes
- `work_time` stored as `H:i:s` — use `work_time_formatted` accessor
- `service_order_items.quantity` is `decimal`
- Pivot `service_order_staff` has `signature_image` column

## API Conventions

- **Sanctum token auth** on all API routes
- **Always use API Resources** for JSON serialization (12 classes)
- **Login** restricted to `owner` role
- **Docs**: Scribe at `/docs` — update with `php artisan scribe:generate`

## RoleMiddleware — Variadic Arguments
```php
Route::middleware(['auth', 'role:owner,co_owner'])->group(...);
Route::middleware(['auth', 'role:owner'])->group(...);
```

## Service Categories

| Code | Full Name |
|------|-----------|
| HV | Hydrovacuum |
| CC | Premium Wash |
| GC | General Cleaning |
| DC | Deep Cleaning |
| CID | Car Interior Detailing |
| — | Poles, Survey |

## Notifications
- **Database channel** (stored in `notifications` table)
- 4 types: `InvoiceOverdue`, `InvoicePaid`, `ServiceOrderDone`, `ServiceOrderInvoiced`
- Triggered by events on status changes

## PDF Generation
- **DOMPDF** — Templates: `resources/views/pdf/{invoice,service-order}.blade.php`

## Settings
```php
AppSetting::get('key', 'default_value');
AppSetting::set('key', 'value');  // Cached via Cache::rememberForever()
```

## Blade Components (13 Breeze)
`application-logo`, `auth-session-status`, `danger-button`, `dropdown`, `dropdown-link`, `input-error`, `input-label`, `modal`, `nav-link`, `primary-button`, `responsive-nav-link`, `secondary-button`, `text-input`

Usage: `<x-primary-button>Save</x-primary-button>`, `<x-modal name="edit-modal">...</x-modal>`

## Conventions Checklist

- [ ] Use `fetch()` for AJAX, not axios
- [ ] Push CSS/JS via `@push('styles')` / `@push('scripts')`
- [ ] No `<form>` for inline edits
- [ ] Remember AreaScope on co_owner queries
- [ ] Use API Resources for all API JSON responses
- [ ] Status transitions validated via `canTransitionTo()`
- [ ] Include CSRF token in fetch headers
- [ ] Mobile-first responsive design
- [ ] 4-space indentation, LF line endings, PSR-12 (Pint)
- [ ] Type hints on all PHP method signatures
