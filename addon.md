# Project Update: New Features Plan (V4 - Laravel 12 Edition)

This document outlines the plan for implementing new schedulers and a notification system, updated to follow **Laravel 12 conventions**.

---

## Feature Set 1: Automated Schedulers

### 1.1. Automatic Service Order Cancellation

*   **Objective**: To automatically change the status of a `ServiceOrder` from `booked` to `cancelled` if it has remained in that state for more than 7 days.
*   **Implementation**:
    1.  **Modify Existing Command**: `app/Console/Commands/AutoCancelOldServiceOrders.php`
        *   **Current Signature**: `service-orders:auto-cancel-old`
        *   **Logic Update**: Modify the `handle()` method to find `ServiceOrder` records where `status` is `ServiceOrder::STATUS_BOOKED` and `created_at` is older than 7 days. Update their `status` to `ServiceOrder::STATUS_CANCELLED`.
        *   **Note**: Ensure the `ServiceOrder` model constants `STATUS_BOOKED` and `STATUS_CANCELLED` are correctly used.
    2.  **Schedule Command (Laravel 12 Method)**:
        *   **File to Edit**: `routes/console.php`
        *   **Action**: The existing schedule for `service-orders:auto-cancel-old` is already defined. Ensure it runs daily.
        *   **Existing Code**:
            ```php
            use Illuminate\Support\Facades\Schedule;

            Schedule::command('service-orders:auto-cancel-old')->daily();
            ```

### 1.2. Automatic Invoice Overdue Status

*   **Objective**: To automatically change the status of an `Invoice` from `unpaid` to `overdue` if the current date has passed the invoice's `due_date`.
*   **Implementation**:
    1.  **Create Command**: `php artisan make:command MarkInvoicesAsOverdue`
        *   **Suggested Signature**: `invoices:mark-overdue` (for consistency with existing commands)
    2.  **Edit File**: `app/Console/Commands/MarkInvoicesAsOverdue.php`
        *   **Logic**: Find `Invoice` records where `status` is 'unpaid' AND `due_date` is in the past. For each one, update the `status` to 'overdue' and dispatch the `InvoiceStatusUpdated` event.
    3.  **Schedule Command (Laravel 12 Method)**:
        *   **File to Edit**: `routes/console.php`
        *   **Action**: Add the schedule for the new command to this file.
        *   **Code**:
            ```php
            use Illuminate\Support\Facades\Schedule;

            // Add this line below the other schedule
            Schedule::command('invoices:mark-overdue')->daily();
            ```

### Server Configuration for Schedulers

* **Action**: The server-side cron job command is the same in Laravel 12.
* **Instruction**: Add this line to your server's crontab:
    ```bash
    * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
    ```

### Database Changes for Schedulers

* **No database schema changes are required for these features.**

---

## Feature Set 2: Expanded Notification System

The implementation for the notification system (Events, Listeners, Notification classes) is modern and fully compatible with Laravel 12.

### Objective

**Notification Triggers:**
1.  `Service Order` status becomes **'invoiced'** -> Notify **Customer**, **Owner**, and **Co-owner**.
2.  `Service Order` status becomes **'done'** -> Notify **Admin**.
3.  `Invoice` status becomes **'paid'** -> Notify **Owner**, **Co-owner**, and **Admin**.
4.  `Invoice` status becomes **'overdue'** -> Notify **Customer**, **Admin**, **Owner**, and **Co-owner**.

### Implementation Steps

1.  **Generate Classes**: The `make` commands for events, listeners, and notifications are unchanged.
    ```bash
    php artisan make:event ServiceOrderStatusUpdated
    php artisan make:listener SendServiceOrderNotification --event=ServiceOrderStatusUpdated
    # ... and so on for other classes.
    ```
2.  **Register Events and Listeners**:
    *   **File to Edit**: `app/Providers/EventServiceProvider.php`
    *   **Action**: Map the events to their listeners in the `$listen` array. This method of explicit registration remains a best practice in Laravel 12.

3.  **Implement Logic**: The logic within the controller, event, listener, and notification classes as described in the V3 plan is correct and does not need to be changed for Laravel 12.
    *   **Note**: The details of the "V3 plan" for notification logic are assumed to be provided separately and are not part of this document.

### Database Changes for Notifications

* The `notifications` table is still required. The commands to create and run the migration are unchanged.
    ```bash
    php artisan notifications:table
    php artisan migrate
    ```

---

## Feature Set 3: Bell Notification UI

### Objective

To enhance the user interface by adding a bell notification icon in the header, providing users with real-time updates and the ability to manage their notifications.

### Implementation Steps

1.  **Frontend UI (`resources/views/layouts/admin.blade.php`)**:
    *   Add a new `nav-item dropdown` element in the header, positioned next to the existing user dropdown (logout button).
    *   Include a bell icon (e.g., SVG) within this `nav-item`.
    *   Add a badge (e.g., `<span>` with a class like `badge bg-red`) to display the count of unread notifications. This badge will be dynamically updated by JavaScript.
    *   Create a dropdown menu (`dropdown-menu`) that will house the list of notifications.
    *   Within the dropdown, include a header (e.g., "Last notifications"), a list group (`list-group`) for individual notification items, and a footer with a "Mark all as read" link/button.
    *   Each notification item should display a title, a brief message, and optionally a timestamp and a link to the related resource.

2.  **Frontend JavaScript (`resources/js/notifications.js`)**:
    *   Create a new JavaScript file (e.g., `resources/js/notifications.js`).
    *   Import this file into `resources/js/app.js` to ensure it's loaded.
    *   Implement functions to:
        *   **Fetch Notifications**: Make an AJAX call to a backend API endpoint (e.g., `GET /api/notifications`) to retrieve the user's notifications. This call should include the authentication token.
        *   **Render Notifications**: Dynamically populate the notification dropdown with fetched notifications. Distinguish between read and unread notifications (e.g., by applying different styles or status dots).
        *   **Update Unread Count**: Update the badge on the bell icon with the current count of unread notifications.
        *   **Mark Single as Read**: Implement an event listener for individual notification items to mark them as read via an AJAX call (e.g., `POST /api/notifications/{id}/mark-as-read`).
        *   **Mark All as Read**: Implement an event listener for the "Mark all as read" button to mark all unread notifications as read via an AJAX call (e.g., `POST /api/notifications/mark-all-as-read`).
        *   (Optional) Implement a polling mechanism to periodically fetch new notifications.

3.  **Backend API Endpoints (`routes/api.php` & `app/Http/Controllers/Api/NotificationController.php`)**:
    *   Create a new API controller (e.g., `NotificationController`) to handle notification-related logic.
    *   Define the following API routes:
        *   `GET /api/notifications`: Returns a list of the authenticated user's notifications. This should leverage Laravel's `Auth::user()->notifications` and `Auth::user()->unreadNotifications`.
        *   `POST /api/notifications/{id}/mark-as-read`: Marks a specific notification as read. This should use `Auth::user()->notifications()->find($id)->markAsRead()`.
        *   `POST /api/notifications/mark-all-as-read`: Marks all unread notifications for the authenticated user as read. This should use `Auth::user()->unreadNotifications->markAsRead()`.
    *   Ensure these routes are protected by appropriate authentication middleware (e.g., `auth:sanctum`).

4.  **Database**: Ensure the `notifications` table is set up by running `php artisan notifications:table` and `php artisan migrate`.