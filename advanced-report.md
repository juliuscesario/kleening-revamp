# Kleening.id Advanced Reports: Detailed Implementation Plan

This document provides a detailed, step-by-step plan for implementing the new advanced reports for the Kleening.id application.

---

## 1. Profitability Report

**Objective:** To analyze the profitability of services, orders, and customer segments.

### To-Do List:

* **Phase 1: Database & Data Entry**
    * [ ] **Create Migration:** Generate a new database migration to add a `cost` column (type: `decimal`, e.g., `15, 2`) to the `services` table.
    * [ ] **Run Migration:** Execute the migration to apply the schema changes to the database.
    * [ ] **Update Service Forms:** Modify the "Create Service" and "Edit Service" forms in the admin panel to include a field for "Service Cost".
    * [ ] **Update Controller Logic:** Adjust the `store` and `update` methods in the `ServiceController` to handle saving the new `cost` value.
    * [ ] **Populate Data:** Manually enter the cost for all existing services.

* **Phase 2: Backend Logic**
    * [ ] **Create Report Controller:** Generate a new `ReportController` using the command: `php artisan make:controller Web/ReportController`.
    * [ ] **Create `profitability` Method:** Inside `ReportController`, create a public method named `profitability`.
    * [ ] **Implement Calculations:**
        * Query all `services` and, for each service, calculate `Total Revenue`, `Total Cost`, and `Total Profit`.
        * Query all `areas` and, for each area, calculate the `Total Profit` by summing up the profit from all its associated service orders.
    * [ ] **Pass Data to View:** Return a view and pass the calculated collections (e.g., `$servicesWithProfit`, `$areasWithProfit`) to it.
    * [ ] **Create Route:** Add a new GET route in `routes/web.php` pointing `/reports/profitability` to the `profitability` method in the `ReportController`.

* **Phase 3: Frontend Display**
    * [ ] **Create Blade View:** Create a new view file at `resources/views/pages/reports/profitability.blade.php`.
    * [ ] **Add Navigation Link:** Add a link to the new report in the main admin sidebar navigation.
    * [ ] **Display Profit per Service:** Create a table in the view to iterate through the `$servicesWithProfit` data and display the name, revenue, cost, and profit for each service.
    * [ ] **Display Profit by Area:** Use a charting library (like ApexCharts, which is included in the Tabler theme) to create a bar chart that visualizes the total profit for each area.

---

## 2. Staff Utilization Report

**Objective:** To provide insights into staff efficiency and workload distribution by calculating the actual time spent on each job.

**Implementation Note:** The system already captures the necessary timestamps automatically. The job duration is the time between the first 'arrival' photo upload and the completion of work proofs ('before' and 'after' photos). No database changes or new buttons for staff are needed.

### To-Do List:

* **Phase 1: Backend Logic**
    * [ ] **Create `staffUtilization` Method:** In the `ReportController`, create a new public method named `staffUtilization`.
    * [ ] **Implement Calculations:**
        * Query all `staff` members.
        * For each staff member, iterate through their assigned `service_orders`.
        * For each `service_order`, find the **Start Time** by getting the `created_at` timestamp of the earliest `work_photos` record with `type = 'arrival'`.
        * The **End Time** is the `work_proof_completed_at` timestamp on the `service_order` itself.
        * Calculate the duration of each job (`End Time` - `Start Time`). If either timestamp is missing, the duration for that job is 0.
        * Sum the durations for all of a staff member's jobs within the filtered period to get their "Total Hours Worked".
        * Optionally, calculate a "Utilization Rate" against a standard work week (e.g., `(Total Hours Worked / 40) * 100`).
    * [ ] **Pass Data to View:** Return a view, passing the collection of staff members with their calculated utilization data.
    * [ ] **Create Route:** Add a new GET route in `routes/web.php` for `/reports/staff-utilization`.

* **Phase 2: Frontend Display**
    * [ ] **Create Blade View:** Create a new view file at `resources/views/pages/reports/staff_utilization.blade.php`.
    * [ ] **Add Navigation Link:** Add a link to the report in the admin sidebar.
    * [ ] **Display Utilization Table:** Create a table to list each staff member along with their calculated "Total Hours Worked" and "Utilization Rate" for the selected period.

---

## 3. Invoice Aging Report

**Objective:** To manage outstanding invoices and improve cash flow.

### To-Do List:

* **Phase 1: Backend Logic**
    * [ ] **Create `invoiceAging` Method:** In the `ReportController`, create a new method `invoiceAging`.
    * [ ] **Implement Logic:**
        * Query all `invoices` that have a status of 'sent' or 'overdue' (or any status that means it's unpaid).
        * For each invoice, calculate the "Days Overdue" by comparing the `due_date` to the current date.
        * Categorize each invoice into an "Aging Bucket" (e.g., 0-30, 31-60, 61-90, 90+ days).
    * [ ] **Pass Data to View:** Return a view and pass the collection of categorized invoices.
    * [ ] **Create Route:** Add a new GET route in `routes/web.php` for `/reports/invoice-aging`.

* **Phase 2: Frontend Display**
    * [ ] **Create Blade View:** Create a new view file at `resources/views/pages/reports/invoice_aging.blade.php`.
    * [ ] **Add Navigation Link:** Add a link to the report in the admin sidebar.
    * [ ] **Display Aging Table:** Create a table that lists each overdue invoice with its number, customer name, amount, due date, days overdue, and the calculated aging bucket.

---

## 4. Customer Retention & Cohort Analysis

**Objective:** To understand customer loyalty and the long-term value of customers.

### To-Do List:

* **Phase 1: Backend Logic**
    * [ ] **Create `customerRetention` Method:** In the `ReportController`, create a new method `customerRetention`.
    * [ ] **Implement Cohort Logic:**
        * Find the first order date for every customer to determine their acquisition month (their "cohort").
        * Group customers by their cohort (e.g., "September 2025").
        * For each cohort, calculate how many of those customers placed another order in the subsequent months (Month 1, Month 2, etc.).
        * Convert these counts to percentages.
    * [ ] **Pass Data to View:** Return a view, passing the structured cohort data (e.g., an array of cohorts, each with an array of retention percentages).
    * [ ] **Create Route:** Add a new GET route in `routes/web.php` for `/reports/customer-retention`.

* **Phase 2: Frontend Display**
    * [ ] **Create Blade View:** Create a new view file at `resources/views/pages/reports/customer_retention.blade.php`.
    * [ ] **Add Navigation Link:** Add a link to the report in the admin sidebar.
    * [ ] **Display Cohort Table:** Create a table (often called a "triangle chart") to display the cohort data.
        * Rows represent the cohort (e.g., "September 2025").
        * Columns represent the months since acquisition (Month 0, Month 1, etc.).
        * The cells will contain the retention percentage. Use color gradients (e.g., green for high retention, red for low) to make it easier to read.