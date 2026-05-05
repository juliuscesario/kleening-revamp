<!-- Full version: .context/archive/architecture-2026-05-04.md -->

# Kleening.id — Architecture

## Overview
Internal cleaning services management platform for **Jabodetabek, Serang, Malang**. Services: Hydrovacuum (HV), Premium Wash (CC), General Cleaning (GC), Deep Cleaning (DC), Car Interior Detailing (CID), Poles, Survey.

## System Architecture
```
Browser/Mobile → Web (Blade SSR) or API (Sanctum) → Laravel 12 App → MySQL
                                                         ↓
                                    19 Web Ctrl + 11 API Ctrl + 3 Commands
                                    17 Models + 10 Policies + 12 API Resources
                                    2 Events + 2 Listeners + 4 Notifications
```

## Key Directories
```
app/
├── Http/Controllers/{Web(19)|Api(11)|Auth(9)}/   # Controllers
├── Http/Resources/                                # 12 API Resource classes
├── Models/ (17) + Scopes/AreaScope.php            # Eloquent models + global scope
├── Policies/ (10)                                 # Authorization
├── Events(2) + Listeners(2)                       # Status-change notifications
├── Notifications/ (4)                             # DB-channel notifications
├── Services/FormOrderParser.php                   # WhatsApp order parser
└── Console/Commands/ (3)                          # Artisan commands

resources/views/
├── layouts/{admin,guest}.blade.php                # Page layouts
├── pages/ (12 feature dirs)                       # Feature pages
├── partials/dashboard/{admin,owner-coowner,staff} # Role-specific widgets
├── components/ (13 Breeze)                        # Reusable components
└── pdf/{invoice,service-order}.blade.php          # DOMPDF templates

database/ — 39 migrations, 5 seeders, schema.md
routes/ — web.php, api.php, auth.php, console.php
```

## Pattern: MVC + Fat Controllers
No service/repository layer. Business logic in controllers. Exceptions:
- `FormOrderParser` — WhatsApp text → structured data + geocoding
- `AppSetting` — Cached key-value store (`Cache::rememberForever`)
- 12 API Resources, 10 Policies, 2 Events/Listeners pairs

## Request Lifecycle
- **Web**: `index.php` → web middleware → auth → role → Controller → Eloquent (AreaScope auto-applied) → Blade → HTML
- **API**: `index.php` → api middleware → auth:sanctum → role → Controller → API Resource → JSON

## Models & Relationships

| Model | Key Relationships |
|-------|-------------------|
| User | `hasOne(Staff)` |
| Customer | `hasMany(Address, ServiceOrder)` — soft deletes |
| Address | `belongsTo(Customer, Area)`, `hasMany(ServiceOrder)` — soft deletes |
| Area | `hasMany(Address)` |
| ServiceCategory | `hasMany(Service)` |
| Service | `belongsTo(ServiceCategory)`, `hasMany(ServiceOrderItem)` |
| Staff | `belongsTo(User, Area)`, `belongsToMany(ServiceOrder)`, `hasMany(StaffOffDay)` |
| ServiceOrder | `belongsTo(Customer, Address, User)`, `hasMany(ServiceOrderItem, WorkPhoto)`, `belongsToMany(Staff)`, `hasOne(Invoice)` |
| ServiceOrderItem | `belongsTo(ServiceOrder, Service)` — decimal qty |
| Invoice | `belongsTo(ServiceOrder)`, `hasMany(Payment)` |
| Payment | `belongsTo(Invoice)` |
| WorkPhoto | `belongsTo(ServiceOrder, User)` |
| Expense | `belongsTo(User, ExpenseCategory)` |
| AppSetting | `get()`/`set()` cached static methods |

**Tables (39)**: users, customers, addresses, areas, service_categories, services, staff, service_orders, service_order_items, service_order_staff (pivot), invoices, payments, work_photos, expenses + categories, staff_off_days, scheduler_logs, app_settings, notifications + Laravel defaults.

**AreaScope**: Applied to Address, Customer, ServiceOrder, Staff, Invoice — filters by `co_owner.area_id`. Bypass: `withoutGlobalScopes()`.

## Frontend Stack

| Library | Purpose |
|---------|---------|
| Tailwind CSS 3.1 | Utility CSS + forms plugin |
| Tabler UI 1.4 | Admin framework |
| Alpine.js 3.4 | Reactivity on Blade |
| jQuery 3.7 + DataTables 2.3 | Server-side tables |
| SweetAlert2 11 | Modals/toasts |
| ApexCharts 5 | Report charts |
| Select2 4.1 | Enhanced dropdowns |
| Toastr 2.1 | Toast notifications |

**AJAX**: `fetch()` (not axios), CSRF from `<meta>`, SweetAlert2 confirmations, no `<form>` for inline edits. CSS/JS via `@push('styles')` / `@push('scripts')`.

## Roles

| Role | Access | Scope |
|------|--------|-------|
| owner | Full system + settings | All areas |
| co_owner | Same as owner | Single area (`area_id`) |
| admin | Planner, SO CRUD, invoices, reports | All areas |
| staff | View assigned SOs, work proof upload | Own assignments |

## Service Order Lifecycle
```
booked → proses → done → invoiced
  │         │
  │         └→ cancelled (owner-only)
  └→ cancelled
```
Terminal: `cancelled`, `done`, `invoiced`. Rules: no new SO if customer has pending SO/overdue invoice; `proses`→`cancelled` needs owner password; work proof = before+after photos; auto-cancel `booked` with work_date > 6 days.

## Events & Notifications
```
ServiceOrderStatusUpdated → SendServiceOrderNotification
InvoiceStatusUpdated → SendInvoiceNotification
```
| Trigger | Recipients |
|---------|-----------|
| Invoice OVERDUE | Customer, admin, owner, co_owner |
| Invoice PAID | Owner, co_owner, admin |
| SO DONE | Admin |
| SO INVOICED | Customer, owner, co_owner |

## Scheduled Tasks
| Command | Frequency | Purpose |
|---------|-----------|---------|
| `service-orders:auto-cancel-old` | Daily | Cancel `booked` SOs > 6 days old |
| `invoices:mark-overdue` | Daily | Mark NEW/SENT past due as OVERDUE |
