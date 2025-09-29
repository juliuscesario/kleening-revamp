<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Auth facade is used in the route closure
use Carbon\Carbon; // Carbon is used in the route closure

// Auth Routes
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Web Controllers
use App\Http\Controllers\Web\AddressController;
use App\Http\Controllers\Web\AreaController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DataTablesController;
use App\Http\Controllers\Web\JsonDataController;
use App\Http\Controllers\Web\ServiceCategoriesController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\ServiceOrderController;
use App\Http\Controllers\Web\StaffController;

use App\Http\Controllers\Web\InvoiceController;
use App\Http\Controllers\Web\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/login');

// Use the new DashboardController for the dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // DataTables and JSON Data Routes
    Route::get('data/areas', [DataTablesController::class, 'areas'])->name('data.areas');
    Route::get('data/service-categories', [DataTablesController::class, 'serviceCategories'])->name('data.service-categories');
    Route::get('data/staff', [DataTablesController::class, 'staff'])->name('data.staff');
    Route::get('data/services', [DataTablesController::class, 'services'])->name('data.services');
    Route::get('data/customers', [DataTablesController::class, 'customers'])->name('data.customers');
    Route::get('data/customers/{customer}/addresses', [JsonDataController::class, 'customerAddresses'])->name('data.customers.addresses');
    Route::get('data/staff/by-area/{area}', [JsonDataController::class, 'staffByArea'])->name('data.staff.by-area');
    Route::get('data/addresses', [DataTablesController::class, 'addresses'])->name('data.addresses');
    Route::get('data/service-orders', [DataTablesController::class, 'serviceOrders'])->name('data.service-orders');
    Route::get('data/invoices', [DataTablesController::class, 'invoices'])->name('data.invoices');
    Route::get('data/payments', [DataTablesController::class, 'payments'])->name('data.payments');

    // Report Data Routes
    Route::get('data/reports/revenue', [DataTablesController::class, 'revenueReportData'])->name('data.reports.revenue');
    Route::get('data/reports/staff-performance', [DataTablesController::class, 'staffPerformanceReportData'])->name('data.reports.staff-performance');
    Route::get('data/reports/customer-growth', [DataTablesController::class, 'customerGrowthReportData'])->name('data.reports.customer-growth');
    Route::get('data/reports/profitability/services', [DataTablesController::class, 'profitabilityServiceData'])->name('data.reports.profitability.services');
    Route::get('data/reports/profitability/areas', [DataTablesController::class, 'profitabilityAreaData'])->name('data.reports.profitability.areas');
    Route::get('data/reports/staff-utilization', [DataTablesController::class, 'staffUtilizationReportData'])->name('data.reports.staff-utilization');
    Route::get('data/reports/revenue/drilldown/{serviceCategory}/trend', [DataTablesController::class, 'revenueTrendChartData'])->name('data.reports.revenue.trend');
    Route::get('data/reports/revenue/drilldown/{serviceCategory}/area', [DataTablesController::class, 'revenueAreaChartData'])->name('data.reports.revenue.area');
    Route::get('data/reports/revenue/drilldown/{serviceCategory}/table', [DataTablesController::class, 'revenueDrilldownTableData'])->name('data.reports.revenue.table');
    Route::get('data/reports/staff/drilldown/{staff}/workload', [DataTablesController::class, 'staffWorkloadChartData'])->name('data.reports.staff.workload');
    Route::get('data/reports/staff/drilldown/{staff}/specialization', [DataTablesController::class, 'staffSpecializationChartData'])->name('data.reports.staff.specialization');
    Route::get('data/reports/staff/drilldown/{staff}/table', [DataTablesController::class, 'staffDrilldownTableData'])->name('data.reports.staff.table');
    Route::get('data/reports/customer/drilldown/{customer}/spending-timeline', [DataTablesController::class, 'customerSpendingTimelineData'])->name('data.reports.customer.spending-timeline');
    Route::get('data/reports/customer/drilldown/{customer}/key-metrics', [DataTablesController::class, 'customerKeyMetricsData'])->name('data.reports.customer.key-metrics');
    Route::get('data/reports/customer/drilldown/{customer}/service-frequency', [DataTablesController::class, 'customerServiceFrequencyData'])->name('data.reports.customer.service-frequency');
    Route::get('data/reports/customer/drilldown/{customer}/order-history', [DataTablesController::class, 'customerOrderHistoryData'])->name('data.reports.customer.order-history');


    // Resource Routes
    Route::resource('areas', AreaController::class)->names('web.areas');
    Route::resource('service-categories', ServiceCategoriesController::class)->names('web.service-categories');
    Route::resource('staff', StaffController::class)->names('web.staff');
    Route::resource('services', ServiceController::class)->names('web.services');
    Route::resource('customers', CustomerController::class)->names('web.customers');
    Route::resource('addresses', AddressController::class)->names('web.addresses');
    Route::resource('service-orders', ServiceOrderController::class)->names('web.service-orders');
    Route::resource('invoices', InvoiceController::class)->names('web.invoices');
    Route::resource('payments', PaymentController::class)->names('web.payments');

    // Reports
    Route::get('reports/revenue', [\App\Http\Controllers\Web\ReportController::class, 'revenue'])->name('web.reports.revenue');
    Route::get('reports/staff-performance', [\App\Http\Controllers\Web\ReportController::class, 'staffPerformance'])->name('web.reports.staff-performance');
    Route::get('reports/customer-growth', [\App\Http\Controllers\Web\ReportController::class, 'customerGrowth'])->name('web.reports.customer-growth');
    Route::get('reports/revenue/drilldown/{serviceCategory}', [\App\Http\Controllers\Web\ReportController::class, 'revenueDrilldown'])->name('web.reports.revenue.drilldown');
    Route::get('reports/staff/drilldown/{staff}', [\App\Http\Controllers\Web\ReportController::class, 'staffDrilldown'])->name('web.reports.staff.drilldown');
    Route::get('reports/customer/drilldown/{customer}', [\App\Http\Controllers\Web\ReportController::class, 'customerDrilldown'])->name('web.reports.customer.drilldown');
    Route::get('reports/profitability', [\App\Http\Controllers\Web\ReportController::class, 'profitability'])->name('web.reports.profitability');
    Route::get('reports/staff-utilization', [\App\Http\Controllers\Web\ReportController::class, 'staffUtilization'])->name('web.reports.staff-utilization');


    // Custom Resource Routes
    Route::put('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('web.invoices.update-status');
    Route::get('service-orders/{serviceOrder}/print', [ServiceOrderController::class, 'printPdf'])->name('web.service-orders.print');

    // Authentication
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__.'/auth.php';