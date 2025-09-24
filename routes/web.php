<?php

use App\Http\Controllers\Web\DataTablesController;
use App\Http\Controllers\Web\AreaController as WebAreaController;
use App\Http\Controllers\Web\StaffController as WebStaffController;
use App\Http\Controllers\Web\ServiceCategoriesController as WebServiceCategoriesController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // ROUTE UNTUK AREAS FUNCTION
    Route::resource('areas', WebAreaController::class)->names('web.areas');
    Route::get('data/areas', [DataTablesController::class, 'areas'])->name('data.areas');
    // ROUTE UNTUK SERVICE CATEGORIES FUNCTION
    Route::resource('service-categories', WebServiceCategoriesController::class)->names('web.service-categories');
    Route::get('data/service-categories', [DataTablesController::class, 'serviceCategories'])->name('data.service-categories');
    // ROUTE UNTUK STAFF FUNCTION
    Route::resource('staff', WebStaffController::class)->names('web.staff');
    Route::get('data/staff', [DataTablesController::class, 'staff'])->name('data.staff');
    // ROUTE UNTUK SERVICES FUNCTION
    Route::resource('services', \App\Http\Controllers\Web\ServiceController::class)->names('web.services');
    Route::get('data/services', [DataTablesController::class, 'services'])->name('data.services');
    // ROUTE UNTUK CUSTOMER & ADDRESS FUNCTION
    Route::resource('customers', \App\Http\Controllers\Web\CustomerController::class)->names('web.customers');
    Route::get('data/customers', [DataTablesController::class, 'customers'])->name('data.customers');
    Route::resource('addresses', \App\Http\Controllers\Web\AddressController::class)->names('web.addresses');
    Route::get('data/addresses', [DataTablesController::class, 'addresses'])->name('data.addresses');
    Route::resource('service-orders', \App\Http\Controllers\Web\ServiceOrderController::class)->names('web.service-orders');
    Route::get('data/service-orders', [DataTablesController::class, 'serviceOrders'])->name('data.service-orders');
    Route::get('service-orders/{serviceOrder}/print', [\App\Http\Controllers\Web\ServiceOrderController::class, 'printPdf'])->name('web.service-orders.print');
    // ROUTE for logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__.'/auth.php';
