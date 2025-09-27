# Kleening.id Owner & Co-owner Business Dashboard

This document outlines the enhanced widgets and reports for the **Owner** and **Co-owner** roles. The primary goal is to provide a clear, high-level overview of business performance, with specific data access rules for each role.

## Role-Based Data Access

This is the core principle for this dashboard:

*   **Owner:** Has a global view. Sees aggregated data from **all areas**. Can filter reports and widgets by one or more specific areas.
*   **Co-owner:** Has a local view. All data displayed is **automatically filtered** to their single assigned `area_id`. They cannot view data from other areas.

---

## Dashboard Widgets

A real-time snapshot of the most important business metrics, tailored by role.

### 1. Key Performance Indicators (KPIs)

At-a-glance metrics showing the current health of the business.
*(For Co-owners, all KPIs reflect their area only. For Owners, they reflect the entire business).*

*   **This Month's Revenue:**
    *   **Description:** Running total of `grand_total` from all invoices with `status = 'paid'` for the current calendar month.
    *   **Data Source:** `invoices` table, joined with `service_orders` to filter by area.

*   **Jobs Completed (This Month):**
    *   **Description:** Total number of service orders with `status = 'invoiced'` or `status = 'confirmed'` (and work_date has passed) for the current month.
    *   **Data Source:** `service_orders` table.

*   **Outstanding Invoices:**
    *   **Description:** The total monetary value of all invoices with `status = 'new'` or `status = 'sent'`.
    *   **Data Source:** `invoices` table.

*   **Overdue Invoices:**
    *   **Description:** The total monetary value of all invoices with `status = 'overdue'`. This is a critical indicator of cash flow health.
    *   **Data Source:** `invoices` table.

*   **New Customers (This Month):**
    *   **Description:** Total count of new customers created during the current month.
    *   **Data Source:** `customers` table (`created_at` field).

### 2. Service Order Funnel

Visualizes the current operational pipeline.

*   **Description:** A bar chart showing the number of `service_orders` in each key status for the last 30 days. This helps identify bottlenecks in the sales and operations process.
*   **Data to Display:**
    *   Count of orders with `status = 'draft'`
    *   Count of orders with `status = 'confirmed'`
    *   Count of orders with `status = 'invoiced'`
*   **Data Source:** `service_orders` table.

### 3. Area Performance Leaderboard (Owner Role Only)

A competitive overview of how different business areas are performing.

*   **Description:** A ranked list of all `areas`, showing key metrics. This is hidden for Co-owners.
*   **Metrics to Display:**
    *   Area Name
    *   This Month's Revenue
    *   Jobs Completed (This Month)
    *   New Customers (This Month)
*   **Data Source:** Aggregated data from `invoices`, `service_orders`, and `customers` grouped by `area_id`.

### 4. Monthly Revenue Chart

A visual representation of revenue trends.

*   **Description:** A bar chart displaying daily paid revenue for the current month.
    *   **For Co-owners:** Shows daily revenue for their area.
    *   **For Owners:** Can be a stacked bar chart showing the revenue contribution of each area per day.
*   **Data Source:** `payments` table (`payment_date` and `amount`).

---

## Reports

In-depth analysis of different business aspects, with data access controlled by role.

### 1. Revenue Report

A comprehensive overview of financial performance.

*   **Description:** Detailed analysis of revenue over a specified period.
*   **Filters:**
    *   Date Range
    *   Area (For Owners: multi-select dropdown. For Co-owners: hidden and fixed to their area).
*   **Data to Display:**
    *   Total Revenue (from paid invoices)
    *   Total `transport_fee` collected
    *   Total `discount` given
    *   Revenue broken down by `service_category`
*   **Visualization:** A line chart showing revenue trends over the selected period.

### 2. Staff Performance Report

Evaluate staff workload and effectiveness.

*   **Description:** Helps identify top-performing staff and workload distribution.
*   **Filters:**
    *   Date Range
    *   Area (Owner vs. Co-owner logic applies)
    *   Staff Member
*   **Data to Display:**
    *   Total jobs completed per staff member.
    *   Total revenue generated per staff member (from their completed jobs).
*   **Visualization:** Bar chart comparing staff members on the selected metric.

### 3. Customer Growth Report

Gain insights into the customer base.

*   **Description:** Tracks customer acquisition and identifies top-spending customers.
*   **Filters:**
    *   Date Range
    *   Area (Owner vs. Co-owner logic applies)
*   **Data to Display:**
    *   **New Customers:** A list of customers created in the date range.
    *   **Top Customers:** A list of customers ranked by total `grand_total` on their invoices in the date range.

### 4. Area Performance Report (Owner Role Only)

A detailed comparative analysis tool for the owner.

*   **Description:** A comprehensive report allowing the owner to compare performance across all business areas. This report is hidden for Co-owners.
*   **Filters:**
    *   Date Range
*   **Data to Display (Table Format):**
    *   Rows: Each `area`
    *   Columns: Key metrics like Total Revenue, Jobs Completed, New Customers, Total Overdue Amount, Average Job Value.
