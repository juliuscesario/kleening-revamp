# Kleening.id — Codebase Structure & Functionality

## Overview

Internal **cleaning services business management platform** for Kleening.id, covering the full operational lifecycle: customer ordering, scheduling, field staff work proof, invoicing, payment tracking, and analytics reporting. Built on **Laravel 12** with PHP 8.2+, PostgreSQL, and Tabler UI. Serves operational areas: **Jabodetabek, Serang, and Malang**.

---

## Directory Structure

### `app/`
| Directory | Contents |
|---|---|
| `Console/Commands/` | 3 artisan commands: `AutoCancelOldServiceOrders`, `MarkInvoicesAsOverdue`, `TestNotification` |
| `Events/` | `InvoiceStatusUpdated`, `ServiceOrderStatusUpdated` |
| `Http/Controllers/` | Web (18), Api (11), Auth (9), base Controller, ProfileController |
| `Http/Middleware/` | `RoleMiddleware` (role-based access: owner, co_owner, admin, staff) |
| `Http/Resources/` | 12 API Resource classes for JSON serialization |
| `Listeners/` | `SendInvoiceNotification`, `SendServiceOrderNotification` |
| `Models/Scopes/` | `AreaScope` (global scope for co_owner area filtering) |
| `Models/` | 17 models |
| `Notifications/` | 4 notifications: InvoiceOverdue, InvoicePaid, ServiceOrderDone, ServiceOrderInvoiced |
| `Policies/` | 10 policies covering all main resources |
| `Providers/` | AppServiceProvider, AuthServiceProvider, EventServiceProvider |
| `View/Components/` | Blade components directory |

### `resources/`
| Directory | Contents |
|---|---|
| `css/` | Tailwind/Tabler CSS entry |
| `js/` | JavaScript entry point |
| `views/` | Blade templates (see Views section) |

### `routes/`
| File | Purpose |
|---|---|
| `web.php` | All web routes with auth + role middleware |
| `api.php` | REST API routes (Sanctum-auth, owner-only login) |
| `auth.php` | Laravel Breeze auth routes |
| `console.php` | Scheduler: daily auto-cancel & overdue marking |

### `database/`
| Directory | Contents |
|---|---|
| `migrations/` | 39 migration files |
| `seeders/` | 5 seeders: DatabaseSeeder, AreaSeeder, UserSeeder, DummyMaster2025Seeder, DailyBookedServiceOrderSeeder |
| `factories/` | Model factories |
| `schema.md` | Database schema documentation |

### `config/`
13 config files: `app`, `auth`, `cache`, `database`, `datatables`, `filesystems`, `logging`, `mail`, `queue`, `sanctum`, `scribe`, `services`, `session`

### `public/`
Standard Laravel public dir with `index.php`, `vendor/` folder.

---

## Controllers

### Web Controllers (18 total)
All under `app/Http/Controllers/Web/`:

| Controller | Purpose |
|---|---|
| **DashboardController** | Role-based dashboard: Owner/co_owner see KPIs (revenue, funnel, area performance), admin sees daily schedule/unassigned/done-not-invoiced widgets, staff sees personal job schedule |
| **PlannerController** | Admin-only operational day planner: view all jobs by date, filter by area, assign staff inline, toggle staff off-days, quick-create service orders |
| **ServiceOrderController** | Full CRUD for service orders with status transitions, PDF printing, validation blocking (pending SOs, overdue invoices), staff assignment |
| **InvoiceController** | Invoice CRUD, PDF download via DOMPDF, status updates, cancellation with SO revert |
| **PaymentController** | Payment recording against invoices, auto-updates invoice paid_amount and status |
| **CustomerController** | Customer listing (DataTables) and detail view with order/billing widgets |
| **AddressController** | Address CRUD for customers, with area assignment and Google Maps links |
| **AreaController** | Area management (CRUD via AJAX/DataTables) |
| **StaffController** | Staff listing (DataTables) with area filter |
| **ServiceController** | Service catalog management with categories |
| **ServiceCategoriesController** | Service category CRUD |
| **ExpenseController** | Expense logging with photo upload, categories management (owner-only) |
| **ReportController** | 10 report views: revenue, expenses, staff-performance, customer-growth, profitability, staff-utilization, invoice-aging, plus drilldown reports |
| **DataTablesController** | Server-side DataTable endpoints for all entities (~1467 lines) |
| **JsonDataController** | JSON endpoints for dynamic dropdowns: customer addresses, staff by area, service search, customer pending orders |
| **NotificationController** | Notification inbox with mark-as-read |
| **SchedulerLogController** | View scheduler execution logs, run artisan commands (whitelist: overdue invoices, auto-cancel SOs) |
| **SettingController** | Owner-only app settings: app name, logo upload, invoice footer text, bank account details |

### API Controllers (11 total)
All under `app/Http/Controllers/Api/`:

| Controller | Key Endpoints |
|---|---|
| **AuthController** | POST /login (phone_number + password, owner-only), POST /logout |
| **AreaController** | API Resource CRUD for areas |
| **ServiceCategoryController** | API Resource CRUD for categories |
| **ServiceController** | API Resource CRUD for services |
| **CustomerController** | API Resource CRUD for customers |
| **AddressController** | indexByCustomer, storeForCustomer, show, update, destroy |
| **StaffController** | CRUD + resign endpoint (creates/deletes linked User account) |
| **ServiceOrderController** | Full CRUD, startWork (upload arrival photo), uploadWorkProof (before/after photos), uploadSignature (customer/staff base64), updateStatus |
| **InvoiceController** | storeFromServiceOrder (auto-generates from SO), index, show, update, destroy |
| **WorkPhotoController** | Store, index, destroy work photos with storage handling |
| **NotificationController** | List notifications, mark as read, mark all as read |

---

## Models (17 total) and Relationships

| Model | Key Fields | Relationships |
|---|---|---|
| **User** | name, phone_number, password, role, area_id | hasOne(Staff) |
| **Customer** | name (auto-uppercase), phone_number | hasMany(Address), hasMany(ServiceOrder), hasManyThrough(Invoice), hasManyThrough(Area) |
| **Address** | customer_id, area_id, label, contact_name, contact_phone, full_address, lokasi, google_maps_link | belongsTo(Customer), belongsTo(Area), hasMany(ServiceOrder) |
| **Area** | name | hasMany(Address), hasManyThrough(ServiceOrder), hasManyThrough(Invoice) |
| **ServiceCategory** | name | hasMany(Service), hasManyThrough(ServiceOrderItem) |
| **Service** | category_id, name, price, cost, description | belongsTo(ServiceCategory), hasMany(ServiceOrderItem) |
| **Staff** | user_id, area_id, name, phone_number, is_active | belongsTo(User), belongsTo(Area), belongsToMany(ServiceOrder), hasMany(StaffOffDay) |
| **StaffOffDay** | staff_id, off_date, notes, created_by | belongsTo(Staff), belongsTo(User as creator) |
| **ServiceOrder** | so_number, customer_id, address_id, work_date, work_time, status, work_notes, staff_notes, created_by, work_proof_completed_at, customer_signature_image | belongsTo(Customer), belongsTo(Address), belongsTo(User as creator), hasMany(ServiceOrderItem), belongsToMany(Staff via service_order_staff pivot), hasOne(Invoice), hasMany(WorkPhoto) |
| **ServiceOrderItem** | service_order_id, service_id, quantity (decimal), price, total | belongsTo(ServiceOrder), belongsTo(Service) |
| **Invoice** | service_order_id, invoice_number, issue_date, due_date, subtotal, discount, discount_type, transport_fee, grand_total, dp_type, dp_value, total_after_dp, paid_amount, status, notes, signature | belongsTo(ServiceOrder), hasMany(Payment) |
| **Payment** | invoice_id, reference_number, amount, payment_date, payment_method, notes | belongsTo(Invoice) |
| **WorkPhoto** | service_order_id, file_path, type (arrival/before/after), uploaded_by | belongsTo(ServiceOrder), belongsTo(User as uploader) |
| **Expense** | user_id, category_id, name, amount, date, description, photo_path | belongsTo(User), belongsTo(ExpenseCategory) |
| **ExpenseCategory** | name | hasMany(Expense) |
| **AppSetting** | key, value | Cached key-value store with `get()`/`set()` static methods |
| **SchedulerLog** | command, start_time, end_time, items_processed | Standalone log model |

### Key Model Behaviors
- **AreaScope**: Applied globally to Address, Customer, ServiceOrder, Staff, Invoice — filters data by co_owner's `area_id`
- **ServiceOrder status lifecycle**: `booked` → `proses` → `done` → `invoiced` (terminal: cancelled, done, invoiced). Transitions validated via `canTransitionTo()` method. Proses→Cancelled requires owner approval.
- **Soft deletes**: Customer, Address

---

## Views (Blade Templates)

### Layouts
- `layouts/admin.blade.php` — Main dashboard layout with sidebar navigation, notification bell, theme toggle, CSRF token. Role-based navigation.
- `layouts/guest.blade.php` — Login/register layout

### Pages
| Directory | Templates |
|---|---|
| `pages/areas/` | index.blade.php |
| `pages/servicecategories/` | index.blade.php |
| `pages/staff/` | index.blade.php |
| `pages/services/` | index.blade.php |
| `pages/customers/` | index.blade.php, detail.blade.php |
| `pages/addresses/` | index.blade.php, create.blade.php |
| `pages/service-orders/` | index.blade.php, create.blade.php, show.blade.php, staff-show.blade.php, _edit_modal_content.blade.php |
| `pages/invoices/` | index.blade.php, show.blade.php, create.blade.php |
| `pages/payments/` | index.blade.php, show.blade.php, create.blade.php |
| `pages/expenses/` | index.blade.php, create.blade.php, categories.blade.php |
| `pages/planner/` | index.blade.php (admin operational planner) |
| `pages/reports/` | revenue, expenses, staff-performance, customer-growth, profitability, staff_utilization, invoice_aging, revenue-drilldown, staff-drilldown, customer-drilldown |
| `pages/notifications/` | index.blade.php |
| `pages/scheduler-logs/` | index.blade.php |
| `settings/` | index.blade.php |

### Other Views
- `dashboard.blade.php` — Routes to role-specific partials
- `welcome.blade.php` — Landing page
- `auth/` — Login, register, password reset views
- `profile/` — Profile edit with partials
- `pdf/` — invoice.blade.php, service-order.blade.php (DOMPDF templates)
- `components/` — 13 reusable Blade components (buttons, modals, dropdowns, inputs)
- `partials/dashboard/` — Role-specific dashboard partials (owner-coowner, admin, staff)
- `scribe/` — API documentation views

---

## Middleware

**`RoleMiddleware`** — Accepts variable role arguments (e.g., `role:owner,co_owner`). Checks if authenticated user's role matches any allowed role. Aborts with 403 if unauthorized.

Used in `web.php` for:
- `role:owner,co_owner` — Scheduler logs access
- `role:owner` — Settings page, expense categories

---

## Architecture Notes

**No dedicated service or repository classes.** The codebase follows a "fat controller" pattern with business logic embedded directly in controllers. However:

- **`AppSetting` model** — Acts as a settings service with cached `get()`/`set()` methods
- **API Resources** (12 classes) — Handle JSON serialization for API responses
- **Policies** (10 files) — Define authorization rules per model
- **Event/Listener system** — 2 events + 2 listeners for notification dispatching on status changes

---

## Database

### Tables (from 39 migrations)
Core tables: `users`, `customers`, `addresses`, `areas`, `service_categories`, `services`, `staff`, `service_orders`, `service_order_items`, `service_order_staff` (pivot), `invoices`, `payments`, `work_photos`, `expense_categories`, `expenses`, `staff_off_days`, `scheduler_logs`, `app_settings`, `notifications`, plus Laravel defaults (cache, jobs, personal_access_tokens).

Notable migration additions:
- Soft deletes to customers and addresses
- Work proof completed_at, signatures, work_time to service_orders
- Discount, DP (down payment), notes to invoices
- Location field (`lokasi`) to addresses
- Reference number to payments
- Cost field to services

---

## Key Business Logic

### Service Order Lifecycle
1. **Booked** — Order created, staff may be assigned
2. **Proses** — Staff starts work (uploads arrival photo)
3. **Done** — Work completed (before + after photos uploaded), staff marks done
4. **Invoiced** — Invoice generated from SO
5. **Terminal states**: Cancelled, Done, Invoiced (cannot be changed further)
6. **Auto-cancellation**: Scheduler cancels `booked` orders older than 7 days

### Role-Based Access Control
- **Owner**: Full access, settings, expense categories, override cancellations
- **Co_owner**: Area-scoped access (via AreaScope), same as owner but limited to their area
- **Admin**: Operational planner, creates/edits SOs, generates invoices, cannot access settings
- **Staff**: Field work only — start work, upload photos, signatures, view assigned SOs

### Business Rules
- **Blocking validation**: Cannot create new SO if customer has pending (non-terminal) SO or overdue invoice
- **Invoice creation**: One invoice per SO; if cancelled, new invoice allowed
- **Payment tracking**: Partial payments supported; invoice auto-marks as PAID when paid_amount >= grand_total
- **DP (Down Payment)**: Invoices support fixed or percentage DP deducted from grand total
- **Discount**: Fixed or percentage discount on subtotal
- **Work photos**: Three types — arrival, before, after. Both before+after required for work_proof_completed_at
- **Signatures**: Customer signature on SO, staff signatures via pivot table

### Scheduled Tasks (Laravel Scheduler)
- `service-orders:auto-cancel-old` — Daily, cancels booked SOs with work_date older than 6 days
- `invoices:mark-overdue` — Daily, marks NEW/SENT invoices past due_date as OVERDUE, fires notification events

### Notifications (Database-channel)
- Invoice OVERDUE: Notifies customer (if has account), admin, owner, co-owner
- Invoice PAID: Notifies owner, co-owner, admin
- Service Order DONE: Notifies admin
- Service Order INVOICED: Notifies customer, owner, co-owner

### Reports
- Revenue by service category with area/time filters
- Expense tracking with photo evidence
- Staff performance with workload and specialization drilldowns
- Customer growth with spending timeline and service frequency
- Profitability by service and area (uses service.cost field)
- Staff utilization
- Invoice aging (Current, 1-30, 31-60, 61-90, 90+ days overdue)

---

## Key Dependencies
- **yajra/laravel-datatables-oracle** — Server-side DataTables processing
- **barryvdh/laravel-dompdf** — PDF generation
- **spatie/image** — Image manipulation
- **laravel/sanctum** — API token authentication
- **knuckleswtf/scribe** — API documentation generation
- **laravel/breeze** — Auth scaffolding
