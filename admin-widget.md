# Kleening.id Admin Dashboard & Reports

This document outlines the recommended widgets and reports for the admin user role in the Kleening.id application. The focus is on providing tools that streamline the daily tasks of creating service orders and managing schedules.

## Admin Dashboard Widgets

The admin dashboard is designed for operational efficiency, providing at-a-glance information and quick access to essential tasks.

### 1. Today's Schedule

A real-time view of the day's jobs.

* **Description:** A list of all `service_orders` scheduled for the current day. Each entry should display the customer's name, the scheduled time, the assigned staff, and the current status of the job (e.g., "Scheduled," "In Progress," "Completed").
* **Data Source:** `service_orders`, `customers`, and `staff` tables.

### 2. Unassigned Jobs

A critical action item to ensure all jobs are covered.

* **Description:** A prominent list or a notification badge that shows the number of `service_orders` that have been created but have not yet been assigned to any staff members. This allows the admin to quickly address any scheduling gaps.
* **Data Source:** `service_orders` and `service_order_staff` tables.

### 3. Recent Activity Feed

A log of recent actions for quick reference.

* **Description:** A feed that shows the most recent `service_orders` that have been created or updated by the logged-in admin. This helps the admin keep track of their work and easily access recently handled orders.
* **Data Source:** `service_orders` table, filtered by the `created_by` field.

### 4. Quick Actions

Easy access to the most common tasks.

* **Description:** A set of prominent buttons for the admin's most frequent actions, such as:
    * **Create New Service Order:** Takes the admin directly to the new order form.
    * **View Full Schedule:** A link to the detailed schedule or calendar view.

---

## Admin Reports

These reports are designed to help the admin with their scheduling and operational responsibilities.

### 1. Daily Job Report

A detailed view of the jobs for any given day.

* **Description:** This report provides a comprehensive list of all jobs for a selected date. It allows the admin to see the full picture of the day's operations and manage the workload effectively.
* **Filters:**
    * Date
    * Status (e.g., Scheduled, In Progress, Completed, Canceled)
    * Area
* **Data to Display:**
    * Service Order ID
    * Customer Name
    * Address
    * Assigned Staff
    * Status

### 2. Staff Availability Report

A tool to simplify the scheduling process.

* **Description:** This report provides a visual overview of staff availability for a selected date range. It can be a calendar or a list view that shows which staff members are already assigned to jobs and who is available. This is invaluable when creating new service orders.
* **Filters:**
    * Date Range
    * Area
* **Data to Display:**
    * A list of staff members with their scheduled jobs for the selected period.

### 3. Service Order History

A complete and searchable record of all orders.

* **Description:** This report provides a searchable and filterable history of all `service_orders`. It's an essential tool for looking up past jobs, answering customer inquiries, and resolving any issues that may arise.
* **Filters:**
    * Date Range
    * Customer Name or Phone Number
    * Staff Member
    * Status

---

## Business & Performance Reports

In addition to operational reports, Admins have access to a suite of business and performance reports to analyze the health of the business. These reports are identical to those available to Owners.

### 1. Revenue Report

A comprehensive overview of financial performance.

*   **Description:** Detailed analysis of revenue over a specified period, broken down by service category.
*   **Filters:**
    *   Date Range
    *   Area (multi-select dropdown)
*   **Data to Display:**
    *   Total Revenue (from paid invoices)
    *   Total `transport_fee` collected
    *   Total `discount` given
    *   Revenue broken down by `service_category`
*   **Interaction:** Clicking on a `service_category` name in the report table navigates to the **Revenue Drilldown** page.

#### Revenue Drilldown View

This view provides a deep dive into the performance of a specific **service category** for the selected time period and area.

*   **Daily Revenue Trend:** A line chart showing the daily revenue generated specifically by this category.
*   **Revenue Distribution by Area:** A bar chart showing how the revenue for this category is distributed across different business areas.
*   **Detailed Transactions:** A table listing every single `service_order_item` that contributed to this category's revenue, including the SO number, customer, work date, and total amount.

### 2. Staff Performance Report

Evaluate staff workload and effectiveness.

*   **Description:** Helps identify top-performing staff and workload distribution.
*   **Filters:**
    *   Date Range
    *   Area
    *   Staff Member
*   **Data to Display:**
    *   Total jobs completed per staff member.
    *   Total revenue generated per staff member (from their completed jobs).
*   **Interaction:** Clicking on a staff member's name navigates to the **Staff Drilldown** page.

#### Staff Drilldown View

This page offers a detailed performance analysis of a single staff member for the selected time period.

*   **Weekly Workload:** A bar chart showing the number of jobs the staff member completed each week.
*   **Service Specialization:** A donut chart illustrating the types of services the staff member performed most frequently (e.g., 60% Cleaning, 30% Laundry, 10% Deep Clean).
*   **Jobs Handled:** A table listing all the service orders assigned to and completed by the staff member in the selected period.

### 3. Customer Growth Report

Gain insights into the customer base.

*   **Description:** Tracks customer acquisition and identifies top-spending customers.
*   **Filters:**
    *   Date Range
    *   Area
*   **Data to Display:**
    *   **New Customers:** A list of customers created in the date range.
    *   **Top Customers:** A list of customers ranked by total `grand_total` on their invoices in the date range.
*   **Interaction:** Clicking on a customer's name navigates to the **Customer Drilldown** page.

#### Customer Drilldown View

This page provides a 360-degree view of a single customer's history and behavior.

*   **Key Metrics:** Cards displaying lifetime stats like Total Spent, Total Orders, Average Days Between Orders, and their most frequently ordered service.
*   **Spending Timeline:** A chart showing the customer's spending month by month.
*   **Service Frequency:** A table showing which services the customer orders most often.
*   **Order History:** A complete, searchable history of all past service orders for the customer.