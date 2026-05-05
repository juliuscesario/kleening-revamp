# Kleening.id — Architecture

## Overview

Internal **cleaning services business management platform** for Kleening.id, covering the full operational lifecycle: customer ordering, scheduling, field staff work proof, invoicing, payment tracking, and analytics reporting. Serves three operational areas: **Jabodetabek, Serang, and Malang**.

Business focuses on professional home cleaning services including **Hydrovacuum (HV), Premium Wash (CC), General Cleaning (GC), Deep Cleaning (DC), Car Interior Detailing (CID), Poles, and Survey**.

---

## System Architecture

```
┌──────────────────────────────────────────────────────────┐
│                    Browser / Mobile                       │
└────────────┬─────────────────────────────┬────────────────┘
             │                             │
       Web (Blade SSR)              REST API (Sanctum)
             │                             │
┌────────────▼─────────────────────────────▼────────────────┐
│                    Laravel 12 App                          │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌───────────┐ │
│  │ Web Ctrl │  │ API Ctrl │  │  Events  │  │ Commands  │ │
│  │  (19)    │  │  (11)    │  │   +      │  │  (3)      │ │
│  │          │  │          │  │Listeners │  │           │ │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘ │
│       │              │             │               │       │
│  ┌────▼──────────────▼─────────────▼───────────────▼─────┐ │
│  │                   Models (17)                         │ │
│  │  + AreaScope (global scope for co_owner filtering)    │ │
│  │  + Policies (10) for authorization                    │ │
│  │  + API Resources (12) for JSON serialization          │ │
│  │  + 1 Service class (FormOrderParser)                  │ │
│  └──────────────────────┬────────────────────────────────┘ │
│                         │                                  │
└─────────────────────────▼──────────────────────────────────┘
                          │
                   ┌──────▼──────┐
                   │   MySQL     │
                   │  (39 migrations)│
                   └─────────────┘
```

---

## Directory Structure

```
app/
├── Console/Commands/          # 3 artisan commands
├── Events/                    # 2 events (Invoice/ServiceOrder status)
├── Http/
│   ├── Controllers/
│   │   ├── Api/               # 11 controllers (Sanctum-auth)
│   │   ├── Auth/              # 9 Breeze auth controllers
│   │   └── Web/               # 19 controllers (DataTablesController ~1467 lines)
│   ├── Middleware/
│   │   └── RoleMiddleware.php # Variadic role args
│   └── Resources/             # 12 API Resource classes
├── Listeners/                 # 2 listeners (notification dispatch)
├── Models/                    # 17 Eloquent models
│   └── Scopes/AreaScope.php   # Co-owner data isolation
├── Notifications/             # 4 notification classes (DB channel)
├── Policies/                  # 10 authorization policies
├── Services/                  # 1 service (FormOrderParser)
└── View/Components/           # Reusable Blade components

resources/
├── css/                       # Tailwind + Tabler entry (app.css)
├── js/                        # Alpine.js, jQuery, DataTables (app.js)
└── views/
    ├── layouts/               # admin.blade.php, guest.blade.php
    ├── pages/                 # 12 feature dirs (customers, invoices, etc.)
    ├── partials/dashboard/    # Role-specific widgets (admin, owner-coowner, staff)
    ├── components/            # 13 Breeze components
    ├── pdf/                   # DOMPDF templates (invoice, service-order)
    ├── auth/                  # 6 Breeze auth views
    ├── profile/               # Profile edit
    └── settings/              # Owner-only settings

database/
├── migrations/                # 39 migration files
├── seeders/                   # 5 seeders
├── factories/                 # Model factories
└── schema.md                  # DBML schema documentation

routes/
├── web.php                    # Web routes (auth + role middleware)
├── api.php                    # REST API routes (Sanctum)
├── auth.php                   # Breeze auth routes
└── console.php                # Scheduler (daily auto-cancel + overdue)
```

---

## Architectural Pattern: MVC + Fat Controllers

Business logic embedded directly in controllers. No dedicated service/repository layer, except:

- **`FormOrderParser`** — Parses WhatsApp text orders into structured data with geocoding
- **`AppSetting` model** — Cached key-value store via `Cache::rememberForever()`
- **API Resources** (12 classes) — JSON serialization
- **Policies** (10 files) — Authorization rules
- **Event/Listener** — 2 events + 2 listeners for status-change notifications

---

## Request Lifecycle

### Web Request
`public/index.php` → middleware (`web`) → `auth` → `role:owner,co_owner,admin,staff` → Controller → Eloquent (AreaScope auto-applied) → Blade → HTML response

### API Request
`public/index.php` → middleware (`api`) → `auth:sanctum` → `role:...` → Controller → API Resource → JSON response

---

## Database Design

### Models and Relationships

| Model | Key Relationships |
|-------|-------------------|
| **User** | `hasOne(Staff)` |
| **Customer** | `hasMany(Address, ServiceOrder)` — Soft deletes |
| **Address** | `belongsTo(Customer, Area)`, `hasMany(ServiceOrder)` — Soft deletes |
| **Area** | `hasMany(Address)` |
| **ServiceCategory** | `hasMany(Service)` |
| **Service** | `belongsTo(ServiceCategory)`, `hasMany(ServiceOrderItem)` |
| **Staff** | `belongsTo(User, Area)`, `belongsToMany(ServiceOrder)`, `hasMany(StaffOffDay)` |
| **ServiceOrder** | `belongsTo(Customer, Address, User)`, `hasMany(ServiceOrderItem, WorkPhoto)`, `belongsToMany(Staff)`, `hasOne(Invoice)` |
| **ServiceOrderItem** | `belongsTo(ServiceOrder, Service)` — decimal `quantity` |
| **Invoice** | `belongsTo(ServiceOrder)`, `hasMany(Payment)` |
| **Payment** | `belongsTo(Invoice)` |
| **WorkPhoto** | `belongsTo(ServiceOrder, User as uploader)` |
| **Expense** | `belongsTo(User, ExpenseCategory)` |
| **AppSetting** | Cached `get()` / `set()` static methods |
| **SchedulerLog** | Standalone log |

### Database Tables (39 migrations)

**Core**: `users`, `customers`, `addresses`, `areas`, `service_categories`, `services`, `staff`, `service_orders`, `service_order_items`, `service_order_staff` (pivot), `invoices`, `payments`, `work_photos`, `expense_categories`, `expenses`, `staff_off_days`, `scheduler_logs`, `app_settings`, `notifications`

**Laravel defaults**: `cache`, `jobs`, `personal_access_tokens`, `password_reset_tokens`, `sessions`

### AreaScope Global Scope

Applied to: **Address, Customer, ServiceOrder, Staff, Invoice** — auto-filters by `co_owner.area_id`. Bypass with `Model::withoutGlobalScopes()`.

---

## Frontend Architecture

### Stack

| Library | Version | Purpose |
|---------|---------|---------|
| Tailwind CSS | 3.1.0 | Utility-first CSS + `@tailwindcss/forms` |
| Tabler UI | 1.4.0 | Admin UI framework |
| Alpine.js | 3.4.2 | Lightweight reactivity on Blade |
| jQuery | 3.7.1 | DataTables dependency |
| DataTables BS5 | 2.3.4 | Server-side data tables |
| SweetAlert2 | 11.23.0 | Modals/toasts |
| ApexCharts | 5.3.5 | Report charts |
| Select2 | 4.1.0-rc.0 | Enhanced dropdowns |
| Toastr | 2.1.4 | Toast notifications |

### Layout & AJAX

- **Layout**: `layouts/admin.blade.php` — sidebar, navbar, notification bell, theme toggle, `@stack('styles')` / `@stack('scripts')`
- **AJAX**: `fetch()` (not axios), CSRF from `<meta>` tag, SweetAlert2 for confirmations, no `<form>` for inline edits

---

## Role-Based Access Control

| Role | Access | Scope |
|------|--------|-------|
| **owner** | Full system + settings + expense categories | All areas |
| **co_owner** | Same as owner | Single area (via `area_id`) |
| **admin** | Operational: planner, SO CRUD, invoices, reports | All areas |
| **staff** | View assigned SOs, start work, upload photos/signatures | Own assignments |

---

## Service Order Lifecycle

```
booked ──→ proses ──→ done ──→ invoiced
   │           │
   │           └──→ cancelled (owner-only from "proses")
   └──→ cancelled
```

**Terminal states**: `cancelled`, `done`, `invoiced`

**Rules**: No new SO if customer has pending SO or overdue invoice; one invoice per SO; `proses`→`cancelled` needs owner password; work proof needs before+after photos; auto-cancel `booked` orders with work_date > 6 days old.

---

## Event-Driven Notifications

```
ServiceOrderStatusUpdated ──→ SendServiceOrderNotification
InvoiceStatusUpdated ──→ SendInvoiceNotification
```

| Trigger | Recipients |
|---------|-----------|
| Invoice OVERDUE | Customer, admin, owner, co_owner |
| Invoice PAID | Owner, co_owner, admin |
| Service Order DONE | Admin |
| Service Order INVOICED | Customer, owner, co_owner |

---

## Scheduled Tasks

| Command | Frequency | Purpose |
|---------|-----------|---------|
| `service-orders:auto-cancel-old` | Daily | Cancel `booked` SOs with work_date > 6 days |
| `invoices:mark-overdue` | Daily | Mark NEW/SENT invoices past due_date as OVERDUE |
