# Memory

## Decisions
<!-- Key architectural and design decisions made during development -->

| Date | Decision | Rationale |
|------|----------|-----------|
| — | "Fat controller" pattern (no service/repository layer) | Simplicity for current team size and codebase scope |
| — | Centralized DataTablesController (~1467 lines) | Single source of truth for all server-side DataTable endpoints |
| — | AreaScope as global scope on 5 models | Automatic data isolation for co_owners without manual filtering |
| — | fetch() over axios for AJAX | Lighter dependency; jQuery already loaded for DataTables |
| — | Tabler UI framework | Pre-built admin components, consistent design system |
| — | Database-channel notifications | No external dependency; simple queue-based delivery |
| — | FormOrderParser service | Parse WhatsApp text orders into structured data with geocoding |
| — | Decimal quantity for service order items | Supports fractional service quantities (migration 2025_12_20) |

## Patterns
<!-- Reusable patterns discovered or established -->

- **Role-based middleware**: `middleware('role:owner,co_owner')` — variadic arguments for flexible access control
- **Blade asset pushing**: `@push('styles')` / `@push('scripts')` for page-specific assets
- **AJAX inline edits**: fetch() + event handlers, no `<form>` elements
- **CSRF token**: `document.querySelector('meta[name="csrf-token"]').content`
- **Cached settings**: `AppSetting::get()/set()` with `Cache::rememberForever()`
- **API Resources**: Consistent JSON serialization for all API responses
- **Event-driven notifications**: Events dispatched on status changes → Listeners send notifications
- **Status lifecycle validation**: `canTransitionTo()` method on ServiceOrder model
- **Blocking validations**: Check for pending SOs and overdue invoices before creation
- **PDF generation**: DOMPDF with Blade templates for invoices and service orders

## Gotchas
<!-- Known issues, workarounds, and things to watch out for -->

1. **AreaScope blocks cross-area queries**: When searching for customers or data across areas (e.g., duplicate check), use `Model::withoutGlobalScopes()` or the query will return nothing for co_owners.

2. **work_time stored as H:i:s**: Not a Carbon datetime. Use `work_time_formatted` accessor for display (returns `H:i`).

3. **ServiceOrder → Invoice relationship**: One-to-one. If SO is cancelled, a new invoice can be created. Use `$so->invoice` to check.

4. **Soft deletes on Customer and Address**: Always consider `->withTrashed()` when querying, especially in SO detail views.

5. **CSRF with fetch**: Must include `X-CSRF-TOKEN` header from meta tag.

6. **Proses → Cancelled requires owner**: Status transition validated with `canTransitionTo()` which checks `user->role === 'owner'`.

7. **Blocking validations**: Cannot create SO if customer has pending SO or overdue invoice. Check before creation.

8. **DataTablesController is massive** (~1467 lines): Be careful when modifying — it handles 14+ entity endpoints in one file.

9. **quantity is decimal** in `service_order_items`: Changed from integer via migration `2025_12_20_145200`. Supports fractional quantities.

10. **DP (Down Payment) on invoices**: Supports `dp_type` (fixed/percentage) and `dp_value`. `total_after_dp` is calculated field.

11. **Axios is in devDependencies but NOT used**: Always use `fetch()` for AJAX calls.

12. **Customer name auto-uppercase**: Model accessor automatically uppercases customer names.

## Dependencies
<!-- External service dependencies and their configurations -->

- **Google Maps**: Geocoding for address location field (`lokasi`) and Google Maps links
- **WhatsApp**: Text-based order input parsed by FormOrderParser service
- **Storage**: Local filesystem for work photos, expense photos, customer signatures, and app logo
- **Database**: MySQL with 39 migrations
- **Queue**: Laravel queue for notification delivery (configured in `config/queue.php`)
- **Mail**: Email configuration in `config/mail.php` (for password resets, etc.)
