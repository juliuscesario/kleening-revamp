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
    Route::get('data/services', [JsonDataController::class, 'services'])->name('data.services');
    Route::get('data/customers', [DataTablesController::class, 'customers'])->name('data.customers');
    Route::get('data/customers/{customer}/addresses', [JsonDataController::class, 'customerAddresses'])->name('data.customers.addresses');
    Route::get('data/staff/by-area/{area}', [JsonDataController::class, 'staffByArea'])->name('data.staff.by-area');
    Route::get('data/addresses', [DataTablesController::class, 'addresses'])->name('data.addresses');
    Route::get('data/service-orders', [DataTablesController::class, 'serviceOrders'])->name('data.service-orders');

    // Resource Routes
    Route::resource('areas', AreaController::class)->names('web.areas');
    Route::resource('service-categories', ServiceCategoriesController::class)->names('web.service-categories');
    Route::resource('staff', StaffController::class)->names('web.staff');
    Route::resource('services', ServiceController::class)->names('web.services');
    Route::resource('customers', CustomerController::class)->names('web.customers');
    Route::resource('addresses', AddressController::class)->names('web.addresses');
    Route::resource('service-orders', ServiceOrderController::class)->names('web.service-orders');

    // Custom Resource Routes
    Route::get('service-orders/{serviceOrder}/print', [ServiceOrderController::class, 'printPdf'])->name('web.service-orders.print');

    // Authentication
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__.'/auth.php';