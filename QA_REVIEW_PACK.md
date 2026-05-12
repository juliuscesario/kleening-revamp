# QA Review Pack — Kleening.id

> Generated: 2026-05-10 | Branch: `main` | HEAD: `fdbabfd`

---

## 1. Folder Structure Overview

```
app/
├── Actions/                          # Command-pattern business logic (7 classes)
│   ├── CompleteServiceOrderAction.php
│   ├── CreateOrderSessionAction.php
│   ├── CreateServiceOrderAction.php
│   ├── DeleteOrderSessionAction.php
│   ├── SyncServiceOrderStatusAction.php
│   ├── UpdateOrderSessionAction.php
│   └── UpdateServiceOrderAction.php
├── Console/Commands/                 # Scheduled artisan commands (4)
├── Events/                           # Domain events (2)
├── Http/
│   ├── Controllers/
│   │   ├── Api/                      # 15 API controllers (Sanctum auth)
│   │   ├── Auth/                     # 9 auth controllers (Breeze-style)
│   │   └── Web/                      # 27 web/Blade controllers
│   ├── Middleware/
│   │   └── RoleMiddleware.php        # Single custom middleware (role gate)
│   ├── Requests/                     # Form request validators (2)
│   └── Resources/                    # API resource transformers (12)
├── Listeners/                        # Event listeners (2)
├── Models/                           # 24 Eloquent models + 1 Scope
│   └── Scopes/AreaScope.php          # Global scope for co_owner area isolation
├── Notifications/                    # 4 notification classes
├── Policies/                         # 11 model policies
├── Providers/                        # 3 service providers
├── Services/                         # Utility services (3)
│   ├── FormOrderParser.php           # Indonesian text → structured order data
│   ├── ImageCompressor.php           # spatie/image v3: max 1200px, JPEG 75%
│   └── PayrollExcelGenerator.php     # PhpSpreadsheet Excel payroll generation
└── View/Components/                  # 2 Blade components

database/
├── migrations/                       # 60 migration files (31 tables)
├── factories/                        # 7 model factories
└── seeders/                          # 8 seeders

resources/views/
├── layouts/
│   ├── admin.blade.php               # Main admin layout (Tabler UI + jQuery + SweetAlert2)
│   └── guest.blade.php
├── pages/                            # 19 page directories
│   ├── service-orders/               # 6 templates (index, create, show, staff-show, etc.)
│   ├── invoices/                     # 3 templates (index, create, show)
│   ├── planner/                      # 2 templates (index, _session_row partial)
│   ├── payroll/                      # 1 template (index)
│   ├── reports/                      # 11 report templates
│   ├── machine-attendances/          # 1 template (index)
│   └── ...                           # customers, staff, areas, services, payments, etc.
├── pdf/
│   ├── invoice.blade.php             # DomPDF invoice template
│   └── service-order.blade.php       # DomPDF service order template
└── partials/dashboard/               # 3 role-specific dashboard partials

routes/
├── web.php                           # Web/Blade routes (~120+ routes)
├── api.php                           # REST API routes (Sanctum)
├── auth.php                          # Auth flow routes (included from web.php)
└── console.php                       # Scheduled commands (3)

config/                               # 13 config files (standard Laravel + datatables, scribe)
```

---

## 2. Database Migrations Summary

### 31 Tables Across 8 Domains

#### Core Business (7 tables)
| Table | Key Columns | Notes |
|---|---|---|
| `areas` | `id`, `name` | Jabodetabek / Serang / Malang |
| `customers` | `id`, `name`, `phone_number`, `last_order_date`, `deleted_at` | Soft deletes |
| `service_categories` | `id`, `name`, `commission_rate` (decimal 5,2, default 10.00) | Commission rate stored as percentage |
| `users` | `id`, `name`, `phone_number`, `password`, `role`, `area_id`, `deleted_at` | Roles: `owner`, `co_owner`, `admin`, `staff`. Soft deletes |
| `staff` | `id`, `user_id`, `area_id`, `name`, `phone_number`, `base_harian` (default 80), `harian_tambahan` (nullable), `is_active`, `deleted_at` | Soft deletes |
| `addresses` | `id`, `customer_id`, `area_id`, `label`, `contact_name`, `contact_phone`, `full_address`, `lokasi` (short location name), `google_maps_link`, `deleted_at` | Soft deletes |
| `services` | `id`, `category_id`, `name`, `price`, `cost`, `description` | |

#### Orders & Invoices (5 tables)
| Table | Key Columns | Notes |
|---|---|---|
| `service_orders` | `id`, `so_number`, `customer_id`, `address_id`, `work_date`, `work_time`, `status`, `work_notes`, `staff_notes`, `is_multi_session`, `work_proof_completed_at`, `customer_signature_image`, `created_by` | Status: `booked → proses → done → invoiced`. `cancelled` is terminal |
| `service_order_items` | `id`, `service_order_id`, `service_id`, `quantity` (decimal), `price`, `total` | No timestamps |
| `service_order_staff` | `service_order_id`, `staff_id`, `signature_image` | **Deprecated** — use `order_session_staff` |
| `invoices` | `id`, `service_order_id`, `reissued_from`, `invoice_number`, `issue_date`, `due_date`, `subtotal`, `discount`, `discount_type`, `grand_total`, `dp_type`, `dp_value`, `total_after_dp`, `paid_amount`, `balance` (stored as: grand_total - paid_amount), `status`, `payment_status`, `notes`, `signature` | `payment_status`: `draft → issued → unpaid → paid` |
| `payments` | `id`, `invoice_id`, `reference_number`, `amount`, `payment_date`, `payment_method`, `notes` | |

#### Multi-Session (4 tables) — Added 2026-05-05
| Table | Key Columns | Notes |
|---|---|---|
| `order_sessions` | `id`, `service_order_id`, `session_number`, `tanggal`, `jam`, `type` (kerja/pickup/delivery/survey/workshop/rework), `status` (booked/proses/done/cancel), `notes`, `started_at`, `completed_at` | Unique: `(service_order_id, session_number)` |
| `order_session_staff` | `id`, `order_session_id`, `staff_id`, `signature_image` | Unique: `(order_session_id, staff_id)`. Replaces deprecated `service_order_staff` |
| `service_order_proofs` | `id`, `service_order_id`, `order_session_id`, `staff_id`, `type`, `file_path` | Before/after photos linked to session |
| `final_order_confirmations` | `id`, `service_order_id` (unique), `content` (longText), `submitted_by`, `submitted_at` | Staff final confirmation |

#### Machine Management (4 tables)
| Table | Key Columns | Notes |
|---|---|---|
| `machine_categories` | `id`, `name`, `slug`, `code_prefix`, `sort_order`, `is_active` | e.g. "Hydrovacuum" → prefix "hv" |
| `machines` | `id`, `code`, `name`, `category_id`, `area_id`, `status` (active/maintenance/retired), `paired_machine_id`, `notes` | Pairing for dual-machine setups |
| `machine_attendances` | `id`, `staff_id`, `date`, `photo_pergi`, `photo_pergi_at`, `photo_pulang`, `photo_pulang_at`, `catatan`, `created_by`, `updated_by` | Unique: `(staff_id, date)` |
| `machine_attendance_items` | `id`, `machine_attendance_id`, `machine_id` | Pivot: which machines checked out |

#### Other Domains
| Domain | Tables |
|---|---|
| Work Documentation | `work_photos` (id, service_order_id, file_path, type: arrival/before/after/signature, uploaded_by) |
| Expense Tracking | `expense_categories`, `expenses` |
| System/Logging | `app_settings` (key-value), `scheduler_logs`, `staff_off_days` |
| Laravel Framework | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`, `personal_access_tokens`, `notifications` |

### Notable Indexes
- `service_orders.work_date` — planner date filtering
- `order_sessions.tanggal` — session date queries
- `machine_attendances.date`, `machine_attendances.staff_id` — attendance lookups
- `invoices.service_order_id` — non-unique (allows reissue chain via `reissued_from`)

---

## 3. Models and Relationships

### Key Models (24 total)

#### Central Model: `ServiceOrder`
```php
// Relationships
→ belongsTo(Customer)->withTrashed()
→ belongsTo(Address)->withTrashed()
→ hasMany(ServiceOrderItem)          // items()
→ belongsToMany(Staff, 'service_order_staff')  // DEPRECATED — use sessions
→ belongsTo(User, 'created_by')      // creator()
→ hasOne(Invoice)                    // latest non-cancelled
→ hasMany(WorkPhoto)                 // workPhotos()
→ hasMany(OrderSession)->orderBy('session_number')  // sessions()
→ hasMany(ServiceOrderProof)         // proofs()
→ hasOne(FinalOrderConfirmation)     // finalOrder()

// Key methods
→ canTransitionTo($status, $user, $password)  // status transition guard
→ hasStaffAssigned($staffId)         // checks across all sessions
→ allAssignedStaff()                 // unique staff across sessions
→ work_time_formatted accessor       // H:i:s → H:i

// Global scopes: AreaScope
// Boot hook: syncs customer.last_order_date on deleted
```

#### `OrderSession` (Session architecture)
```php
→ belongsTo(ServiceOrder)
→ belongsToMany(Staff, 'order_session_staff')->withPivot('signature_image')->withTimestamps()
// Accessors: type_label, status_label (human-readable)
```

#### `Invoice`
```php
→ belongsTo(ServiceOrder)
→ hasMany(Payment)
→ belongsTo(Invoice, 'reissued_from')    // reissueOrigin()
→ hasOne(Invoice, 'reissued_from')       // reissuedInvoice()
// Constants: STATUS_NEW, STATUS_SENT, STATUS_OVERDUE, STATUS_PAID, STATUS_CANCELLED
// Default: status = 'sent'
// Global scopes: AreaScope
```

#### `Staff`
```php
→ belongsTo(Area)
→ belongsTo(User)
→ belongsToMany(ServiceOrder, 'service_order_staff')  // DEPRECATED
→ belongsToMany(OrderSession, 'order_session_staff')  // CURRENT
→ hasMany(StaffOffDay)
→ hasMany(MachineAttendance)
// Global scopes: AreaScope, SoftDeletes
```

#### `MachineAttendance`
```php
→ belongsTo(Staff)
→ hasMany(MachineAttendanceItem)
→ belongsToMany(Machine, 'machine_attendance_items')
→ belongsTo(User, 'created_by')
→ belongsTo(User, 'updated_by')
// Static: hasActiveAttendanceToday($staffId), getOpenAttendanceToday($staffId)
```

### Models with AreaScope (co_owner isolation)
`Address`, `Customer`, `Invoice`, `ServiceOrder`, `Staff`

### Models with SoftDeletes
`Address`, `Customer`, `ServiceOrder`, `Staff`, `User`

### Models with $timestamps = false
`ServiceOrderItem`, `WorkPhoto`

---

## 4. Route List for New Features

### Critical Route Patterns for Adding Features

#### Web Routes (session auth)
```
GET/POST/PUT/DELETE  /{resource}                    → Full resource CRUD (resource:areas, resource:staff, etc.)
GET/POST             /service-orders/{id}/{action}   → Custom action on SO
GET/POST             /invoices/{id}/{action}         → Custom action on Invoice
GET/POST             /planner/{action}               → Planner actions
GET                  /reports/{report-type}          → Report pages
POST                 /planner/session/{session}/{action}  → Session inline edits
```

#### API Routes (Sanctum token auth)
```
apiResource: /api/{resource}                        → RESTful CRUD
POST         /api/service-orders/{id}/{action}      → Custom SO actions
POST         /api/machine-attendance/{action}       → Machine check-in/out
```

#### DataTables/AJAX Data Endpoints
```
GET /data/{resource}                                 → JSON data for DataTables
GET /data/reports/{report-type}                      → Chart/table data for reports
GET /data/reports/{entity}/drilldown/{id}/{metric}   → Drilldown data
```

#### Middleware Applied to Route Groups
| Middleware | Routes |
|---|---|
| `auth` | All authenticated routes |
| `role:owner,admin` | Planner mutations, session CRUD |
| `role:owner` | Machine CRUD, settings, expense categories |
| `role:owner,co_owner` | Reports, scheduler logs |
| `role:owner,co_owner,admin,staff` | Work photos, machine attendance |
| `role:owner,co_owner,admin` | Work photo deletion |

---

## 5. Controllers — Detailed Breakdown

### Service Orders
| Controller | File | Key Methods |
|---|---|---|
| `Web\ServiceOrderController` | `app/Http/Controllers/Web/ServiceOrderController.php` | `index`, `create`, `show`, `store` (with blocking checks), `update` (invoice-paid gate), `unassigned`, `updateStatus`, `markComplete`, `cancel`, `printPdf` |
| `Api\ServiceOrderController` | `app/Http/Controllers/Api/ServiceOrderController.php` | `index` (staff-scoped), `store`, `show`, `update`, `updateStatus`, `startWork` (Mesin Pergi gate), `uploadWorkProof` (Mesin Pergi gate + compression), `completeWork`, `submitCustomerSignature` (base64 → WorkPhoto → mark sessions done) |

**Blocking checks on create:** Customer cannot have pending SOs or overdue invoices.
**Mesin Pergi gate:** Staff must have active machine attendance before starting work or uploading proofs.

### Order Sessions
| Controller | File | Key Methods |
|---|---|---|
| `Web\OrderSessionController` | `app/Http/Controllers/Web/OrderSessionController.php` | `list` (JSON), `store` → `CreateOrderSessionAction`, `update` → `UpdateOrderSessionAction`, `destroy` → `DeleteOrderSessionAction` |

**Rules:** Cannot delete last session. Session types: `kerja`, `pickup`, `delivery`, `survey`, `workshop`, `rework`.

### Machine Attendance
| Controller | File | Key Methods |
|---|---|---|
| `Api\MachineAttendanceController` | `app/Http/Controllers/Api/MachineAttendanceController.php` | `status` (today's state), `availableMachines` (filtered by area, marks checked-out), `pergi` (checkout with race-condition protection in DB transaction), `pulang` (return machines) |
| `Api\MachineAttendanceManageController` | `app/Http/Controllers/Api/MachineAttendanceManageController.php` | `index` (with filters), `show`, `update` (notes), `forceClose` (owner/co-owner only), `destroy` (+ cleanup photos) |
| `Web\MachineAttendanceManageController` | `app/Http/Controllers/Web/MachineAttendanceManageController.php` | `index` (renders management page) |

**Race condition protection:** `pergi()` double-checks inside DB transaction that machines aren't already checked out.

### Payroll
| Controller | File | Key Methods |
|---|---|---|
| `Web\PayrollController` | `app/Http/Controllers/Web/PayrollController.php` | `index` (auto-selects period: 1=1-10, 2=11-20, 3=21-EOM), `download` (generates Excel via `PayrollExcelGenerator`) |

**Session-based:** Each `OrderSession` = one row. Split-jobs: items with different commission rates → multiple rows per session. Discount prorated. Transport fee only on first session. Harian: `base_harian` on first job of day, `harian_tambahan` on subsequent. Lateness: compares `work_time` vs arrival photo timestamp.

### Invoice
| Controller | File | Key Methods |
|---|---|---|
| `Web\InvoiceController` | `app/Http/Controllers/Web/InvoiceController.php` | `index`, `create` (gate: all sessions done), `store` (DP/discount calc, SO→invoiced), `updateStatus`, `show`, `downloadPdf` (DomPDF), `viewPdf`, `destroy` (revert SO→done), `reissue` (cancel old + create new with `reissued_from` FK) |
| `Api\InvoiceController` | `app/Http/Controllers/Api/InvoiceController.php` | `storeFromServiceOrder` (from completed SO), `index`, `show`, `update`, `destroy` |

**Creation gate:** SO status must be `done` AND all non-cancel sessions must be `done`. One active invoice per SO.

### Staff Dashboard
| Controller | File | Key Methods |
|---|---|---|
| `Web\DashboardController` | `app/Http/Controllers/Web/DashboardController.php` | `index` (role-branch: admin→planner redirect, owner/co_owner→KPIs+charts, staff→session schedules), `getToken` (Sanctum token) |

**Staff section queries:** `OrderSession` via `order_session_staff` (not `ServiceOrder`). Shows today/tomorrow/past/cancelled/done sessions. Machine attendance status included.

### Reports
| Controller | File | Key Methods |
|---|---|---|
| `Web\ReportController` | `app/Http/Controllers/Web/ReportController.php` | `revenue`, `expenses`, `staffPerformance`, `customerGrowth`, `profitability`, `staffUtilization`, `invoiceAging` (days overdue buckets), `revenueDrilldown`, `staffDrilldown`, `customerDrilldown` |
| `Web\ReportMachineAttendanceController` | `app/Http/Controllers/Web/ReportMachineAttendanceController.php` | `index` (report page) |
| `Web\LaporanKinerjaAdminController` | `app/Http/Controllers/Web/LaporanKinerjaAdminController.php` | `index` |
| `Web\DataTablesController` | `app/Http/Controllers/Web/DataTablesController.php` | 18+ AJAX data endpoints for DataTables and report charts |

**Access:** All report pages gated by `view-reports` gate (owner/co_owner only). DataTables endpoints auth-only.

---

## 6. Middleware / Permissions Summary

### Custom Middleware
**`RoleMiddleware`** (`app/Http/Middleware/RoleMiddleware.php`)
- Registered as `role` alias in `bootstrap/app.php`
- Checks `auth()->user()->role` against variadic allowed roles
- Returns 403 on mismatch

### Authentication Guards
- **Default:** `web` (session-based, `config/auth.php`)
- **API:** `auth:sanctum` (token-based, Laravel Sanctum)
- **User provider:** Eloquent, `App\Models\User`

### Authorization (AuthServiceProvider)
```php
// Super card: owners bypass all policies
Gate::before(fn ($user) => $user->role === 'owner' ? true : null);

// Named gates
Gate::define('manage-master-data', fn ($user) => in_array($user->role, ['owner', 'co_owner']));
Gate::define('view-reports', fn ($user) => in_array($user->role, ['owner', 'co_owner']));
```

### 11 Policies
| Model | Policy | Key Pattern |
|---|---|---|
| ServiceOrder | ServiceOrderPolicy | Staff → assigned only; co_owner → area-scoped |
| ServiceCategory | ServiceCategoryPolicy | Owner only (all others return false) |
| Customer | CustomerPolicy | co_owner → area-scoped via addresses |
| Staff | StaffPolicy | co_owner → area-scoped; delete → false for all |
| Area | AreaPolicy | Owner only; delete → false |
| Service | ServicePolicy | Standard CRUD |
| WorkPhoto | WorkPhotoPolicy | Owner bypass; staff → own SO |
| Invoice | InvoicePolicy | co_owner → area-scoped via serviceOrder.address |
| Address | AddressPolicy | Owner bypass; co_owner → area-scoped |
| Payment | PaymentPolicy | co_owner → area-scoped |
| MachineAttendance | MachineAttendancePolicy | Owner/co_owner only |

### Role Hierarchy
| Role | Capabilities |
|---|---|
| `owner` | Full access, bypasses all policies via `Gate::before` |
| `co_owner` | Area-scoped manager — sees only their `area_id` records |
| `admin` | Operational manager — sees all areas, cannot manage machines/settings |
| `staff` | Field worker — only sees assigned orders, can upload photos |

---

## 7. Scheduled Commands

Registered in `routes/console.php`:

| Command | Signature | Schedule | What It Does |
|---|---|---|---|
| `AutoCancelOldServiceOrders` | `service-orders:auto-cancel-old` | Daily | Cancels `booked` SOs where `work_date` > 6 days ago. Creates `SchedulerLog`. |
| `MarkInvoicesAsOverdue` | `invoices:mark-overdue` | Daily | Marks `new`/`sent` invoices with `due_date` < today as `overdue`. Fires `InvoiceStatusUpdated` event. Creates `SchedulerLog`. |
| `AutoCloseMachineAttendance` | `machine-attendance:auto-close` | Daily at 23:30 | Closes open attendances (`photo_pulang_at` = null) for dates ≤ today. Appends auto-close note. |

**CRON requirement:** Server must run `php artisan schedule:run` every minute. The 23:30 auto-close MUST be configured.

---

## 8. Important Business Rules Implemented

### Status Lifecycle
```
ServiceOrder: booked → proses → done → invoiced (terminal)
                          ↓
                    cancelled (terminal, owner-only from proses)

OrderSession: booked → proses → done
                        ↓
                   cancel

Invoice: new → sent → overdue → paid
                   ↓
              cancelled (on reissue/delete)
```

### Parent-Child Status Sync (`SyncServiceOrderStatusAction`)
After any session update, parent SO status is recalculated:
- No sessions → `booked`
- All cancel → `cancel`
- All non-cancel done → `done`
- Any proses OR mix of done+booked → `proses`
- All booked (or cancel+booked) → `booked`

### Order Creation Guards
1. **Pending SO gate:** Customer cannot have non-terminal SO (not `done`/`cancelled`/`invoiced`)
2. **Overdue invoice gate:** Customer cannot have unpaid, overdue invoices

### Invoice Rules
- One active (non-cancelled) invoice per SO
- Cannot edit SO if its invoice is `paid`
- Cannot delete invoice if `paid`, `cancelled`, or has payments
- Reissue: cancels old, creates new with `reissued_from` FK, SO cycles `done → invoiced`
- Payment: invoice auto-transitions to `paid` when `round(paid_amount) >= round(grand_total)`

### Mesin Pergi Gate
Staff must have active "Mesin Pergi" (machine checkout) today before:
- Starting work (`startWork`)
- Uploading work proofs (`uploadWorkProof`)
- Viewing SO detail page (web `staff-show` redirects if no attendance)

### Commission Rate System
- Stored in `service_categories.commission_rate` as percentage (e.g., 10.00 = 10%)
- Fallback: 10% if category not found
- Payroll split-jobs: items grouped by com rate → multiple rows, one per group
- Rates sorted ascending: 0.10 → 0.15 → 0.30
- Rounding correction applied to last group

### Payroll Rules
- Session-based (not SO-based): each session = one row
- Multi-session omset spread: `grand_total / total_sessions`
- Transport fee: first session only
- Harian: `base_harian` (first job of day), `harian_tambahan` (subsequent)
- Periods: P1=1st-10th, P2=11th-20th, P3=21st-end of month
- Only `proses`/`done` sessions counted (not `booked`/`cancel`)

### Lateness Penalties (Denda)
| Column | Rule | Penalty |
|---|---|---|
| TIME (R) | Late > 0 min → orange; > 15 → red; > 300 → yellow; no arrival → pink | Visual only |
| DENDA (S) | Late > 15 min | -10 |
| BEFORE (U) | No before photo on first row of SO | -10 |
| AFTER (W) | No after photo on first row of SO | -10 |
| MESIN PERGI (Y) | No pergi photo on first row of date | -10 |
| MESIN PULANG (AA) | No pulang photo on first row of date | -10 |

### Customer Signature
- Submitted after after-photo upload on staff SO detail page
- Base64 decoded → saved as `WorkPhoto` with `type='signature'`
- Marks ALL incomplete sessions (`booked`/`proses`) as `done`

### Area Scope (Global Scope)
Automatically filters queries for `co_owner` users by `area_id`:
- Direct: `staff`, `addresses` → `where('area_id', $areaId)`
- Through relationship: `customers` → `whereHas('addresses')`, `service_orders` → `whereHas('address')`, `invoices` → `whereHas('serviceOrder.address')`
- Cross-area queries require `withoutGlobalScopes()`

### Work Date Change Restrictions
- Only `admin` or `owner` can change `work_date`
- Only when status is `booked` or `proses`
- Cannot backdate (new date ≥ today)

---

## 9. Known Risky Areas

### 🔴 High Risk

1. **`service_order_staff` vs `order_session_staff` pivot confusion**
   - `service_order_staff` is **deprecated** but still exists in DB and has relationships on `ServiceOrder` and `Staff` models
   - New code MUST use `order_session_staff` via `OrderSession` model
   - Memory: "Staff dashboard migrated to sessions" (3 days old)

2. **Race condition in machine checkout (`pergi`)**
   - `Api\MachineAttendanceController::pergi()` has double-check inside DB transaction
   - If this pattern is copied without the double-check, race conditions will occur
   - File: `app/Http/Controllers/Api/MachineAttendanceController.php` ~line 100

3. **Payroll Excel generation — PHP array key coercion**
   - Commission rate groups use **string keys** (`"0.10"`, `"0.15"`, `"0.30"`) to avoid PHP float→int coercion
   - If changed to float keys, grouping will break silently
   - File: `app/Http/Controllers/Web/PayrollController.php` ~line 134

4. **AreaScope on `co_owner` with no `area_id`**
   - If a co_owner has no area_id assigned, `AreaScope` forces `whereRaw('1 = 0')` → returns nothing
   - This is intentional but can be confusing when debugging empty results

5. **Invoice uniqueness — non-unique `service_order_id`**
   - Migration `2026_05_01_173321` dropped the unique constraint to allow reissue chain
   - Old code that assumed one invoice per SO may return multiple results
   - Controller handles this by redirecting to existing active invoice

6. **Image compression — 128MB max upload**
   - `startWork` and `uploadWorkProof` accept up to 128MB (HEIC/HEIF support)
   - Compression runs synchronously — large files may timeout on slow servers

### 🟡 Medium Risk

7. **Session status sync creates infinite loop potential**
   - `UpdateOrderSessionAction` → `SyncServiceOrderStatusAction` → updates SO status
   - If SO update ever triggers session update, could loop (currently doesn't, but fragile)

8. **Customer `last_order_date` sync**
   - Triggered on SO create/update/delete via `withoutEvents()` to prevent recursion
   - If someone removes `withoutEvents()`, infinite recursion will occur

9. **DomPDF limitations**
   - Invoice PDF uses DomPDF — CSS support is limited (no flexbox, no modern CSS grid)
   - Memory: "DomPDF Renderer Constraints" — known issues with layout

10. **`work_time` stored as H:i:s**
    - Controllers accept H:i format and append `:00`
    - If direct DB insertion bypasses normalization, display will break

11. **Planner inline edit DOM updates**
    - All inline edits use in-place DOM updates (no reload)
    - Memory: "Operational Planner inline edit polish" — if adding new edits, must follow same pattern

### 🟢 Low Risk (Watch Items)

12. **Auto-cancel threshold:** 6 days — business may want to change this
13. **Auto-close machine attendance at 23:30:** depends on server timezone (Asia/Jakarta assumed)
14. **Payroll period auto-selection:** based on server's `now()` — may not match business calendar if periods shift mid-month

---

## 10. Files Changed in Latest Implementation

### Last 5 Commits (88 files changed, +7883 / -978)

#### Commit `fdbabfd` — "add more denda on payroll. need check all the commit final"
| File | Changes |
|---|---|
| `app/Http/Controllers/Web/PayrollController.php` | +denda logic for before/after/mesin photos |
| `app/Services/PayrollExcelGenerator.php` | +columns T-AA for before/after/mesin pergi/pulang checks with penalty flags |
| `resources/views/pages/service-orders/staff-show.blade.php` | Signature canvas updates |

#### Commit `4479818` — "final order confirmation staff"
| File | Changes |
|---|---|
| `app/Http/Controllers/Web/FinalOrderController.php` | New controller for staff final order confirmation |
| `app/Models/FinalOrderConfirmation.php` | New model |
| `database/migrations/2026_05_09_204630_create_final_order_confirmations_table.php` | New migration |
| `resources/views/pages/service-orders/staff-show.blade.php` | Final confirmation UI |

#### Commit `f206872` — "absensi mesin. harus setel cron job 23.30 auto resolve, fix invoice unique error"
| File | Changes |
|---|---|
| `app/Http/Controllers/Api/MachineAttendanceController.php` | New — staff check-in/out |
| `app/Http/Controllers/Api/MachineAttendanceManageController.php` | New — admin management |
| `app/Http/Controllers/Web/MachineAttendanceManageController.php` | New — web page |
| `app/Http/Controllers/Web/ReportMachineAttendanceController.php` | New — report page |
| `app/Models/MachineCategory.php` | New model |
| `app/Models/Machine.php` | New model |
| `app/Models/MachineAttendance.php` | New model |
| `app/Models/MachineAttendanceItem.php` | New model |
| `app/Console/Commands/AutoCloseMachineAttendance.php` | New scheduled command |
| `database/migrations/` (4 files) | machine_categories, machines, machine_attendances, machine_attendance_items |
| `resources/views/pages/machine-attendances/` | New views |
| `resources/views/pages/reports/machine-attendance.blade.php` | New report view |
| `public/js/machine-attendance.js` | New JS for attendance flow |
| `app/Policies/MachineAttendancePolicy.php` | New policy |
| `database/seeders/` | Machine category seeder |

#### Commit `7937c40` — "Add multi session child, SO parent. saat naik ad php migrate backfill semua so ke session"
| File | Changes |
|---|---|
| `app/Actions/CreateOrderSessionAction.php` | New |
| `app/Actions/UpdateOrderSessionAction.php` | New |
| `app/Actions/DeleteOrderSessionAction.php` | New |
| `app/Actions/SyncServiceOrderStatusAction.php` | New |
| `app/Actions/UpdateServiceOrderAction.php` | Updated for sessions |
| `app/Actions/CreateServiceOrderAction.php` | Updated to create Session 1 |
| `app/Models/OrderSession.php` | New model |
| `app/Http/Controllers/Web/OrderSessionController.php` | New |
| `app/Http/Controllers/Web/PlannerController.php` | Updated for sessions |
| `app/Http/Controllers/Web/ServiceOrderController.php` | Updated for sessions |
| `app/Http/Controllers/Web/DashboardController.php` | Updated for sessions |
| `app/Http/Controllers/Web/PayrollController.php` | Updated for sessions |
| `database/migrations/` (5 files) | order_sessions, order_session_staff, is_multi_session, backfill migrations |
| `resources/views/pages/planner/` | Updated views + _session_row partial |
| `resources/views/partials/dashboard/staff.blade.php` | Updated for sessions |

#### Commit `1e1c420` — "planner add no hp and truncat notes"
| File | Changes |
|---|---|
| `resources/views/pages/planner/index.blade.php` | +phone number display, truncated notes |
| `.context/` files, `AGENTS.md`, `CLAUDE.md` | Documentation updates |

### Current Working Tree
- **Clean** — no uncommitted changes

---

## Quick Reference

### Key Service Categories & Default Commission
| Category | Default Rate |
|---|---|
| General Cleaning, Deep Cleaning | 30% |
| Poles, Poles Lantai | 15% |
| Hydrovacuum, Premium Wash, CID, Package, Add On | 10% |

### Status Flow Quick Reference
```
SO: booked → proses → done → invoiced → (payment flow)
                    ↓
              cancelled (owner-only from proses)

Session: booked → proses → done
                   ↓
              cancel

Invoice: sent → overdue → paid
               ↓
          cancelled (on reissue)
```

### Areas
- Jabodetabek
- Serang
- Malang

### Key URLs (Development)
- Login: `/login`
- Dashboard: `/dashboard`
- Planner: `/planner`
- Payroll: `/payroll`
- Service Orders: `/service-orders`
- Invoices: `/invoices`
- Reports: `/reports/{type}`
- Machine Attendance: `/master-data/machine-attendances`
- Settings: `/settings` (owner only)

---

*This QA Review Pack is generated from the codebase state at commit `fdbabfd` on 2026-05-10. Verify against current code before asserting file-level facts — code may have changed since this snapshot.*
