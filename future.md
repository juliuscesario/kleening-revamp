# Kleening.id: Strategic Growth Features Proposal

To enhance the value of the Kleening.id platform, this document proposes several new features designed to improve customer retention, streamline operations, and provide deeper business insights. These features are grouped into four key areas of growth.

---

## 1. Customer Relationship & Marketing Automation

**Goal:** Increase customer lifetime value and encourage repeat business.

### Feature 1.1: Customer Loyalty Program
* **Objective:** To reward repeat customers and incentivize them to continue using our services.
* **Proposed Solution:**
    * Implement a simple points-based system. Customers earn points for every completed and paid service order.
    * These points can be redeemed for discounts on future bookings.
    * The customer's current point balance will be visible on their profile.
* **Database Changes:**
    * Add a `loyalty_points` (integer) column to the `customers` table.

### Feature 1.2: Automated Re-engagement Campaigns
* **Objective:** To proactively bring back customers who have not ordered in a while.
* **Proposed Solution:**
    * Create an automated scheduler that runs monthly.
    * It will identify customers who have not placed an order in the last 90 days.
    * The system will automatically send these customers a "We Miss You!" email or WhatsApp message, potentially including a unique discount code to encourage a new booking.
* **Database Changes:**
    * Add a `last_order_date` (timestamp) column to the `customers` table (this can be updated automatically when a service order is completed).

---

## 2. Staff Management & Performance

**Goal:** Simplify payroll, motivate staff, and improve service quality.

### Feature 2.1: Automated Staff Commission Calculation
* **Objective:** To eliminate manual payroll calculations and provide staff with transparent earnings reports.
* **Proposed Solution:**
    * Use the existing `komisi` (commission) field on services to automatically calculate the commission for each staff member on a completed job.
    * Create a "My Earnings" dashboard for staff to see their commission per job and their total monthly earnings.
    * Admins will get a summary report for easy payroll processing.
* **Database Changes:**
    * Create a new `commissions` table (`id`, `service_order_id`, `staff_id`, `amount`, `calculation_date`).

### Feature 2.2: Customer Feedback & Staff Rating System
* **Objective:** To gather valuable feedback for quality control and identify top-performing staff.
* **Proposed Solution:**
    * After a service order is marked 'completed', automatically send an email or notification to the customer asking for a rating (1-5 stars) and a brief review of the service and the staff who performed it.
    * Display average ratings on a staff performance dashboard for management.
* **Database Changes:**
    * Create a new `reviews` table (`id`, `service_order_id`, `customer_id`, `staff_id`, `rating`, `comment`).

---

## 3. Advanced Financial Reporting

**Goal:** To provide a clearer understanding of the business's financial health and profitability.

### Feature 3.1: Service Profitability Analysis
* **Objective:** To identify which services are the most and least profitable.
* **Proposed Solution:**
    * Add a `cost` field to the `services` table to represent the direct cost of providing that service (e.g., materials).
    * Generate new financial reports that show not just revenue, but also the **Gross Profit** for each service, service category, and even individual service orders.
* **Database Changes:**
    * Add a `cost` (decimal) column to the `services` table.

### Feature 3.2: Expense Tracking Module
* **Objective:** To get a complete financial picture by tracking all business expenses in one place.
* **Proposed Solution:**
    * Create a new section in the admin panel where the owner or admin can log operational expenses (e.g., cleaning supply purchases, marketing costs, vehicle maintenance).
    * The main dashboard can then show a true Profit & Loss overview (Total Revenue - Total Commissions - Total Expenses).
* **Database Changes:**
    * Create a new `expenses` table (`id`, `expense_category`, `description`, `amount`, `expense_date`).

---

## 4. Operational Efficiency

**Goal:** To streamline daily tasks and ensure standard procedures are followed.

### Feature 4.1: Automated Photo Upload Reminders
* **Objective:** To ensure staff consistently upload the required "before and after" photos for quality assurance and dispute resolution.
* **Proposed Solution:**
    * Create a daily scheduler that checks all service orders marked as 'completed' on the previous day.
    * If a completed order is missing its corresponding photos in the `work_photos` table, the system will send an automatic reminder notification to the assigned staff member.
* **Database Changes:**
    * No new changes are required; this feature uses the existing table structure.