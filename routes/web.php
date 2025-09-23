<?php

use App\Http\Controllers\Web\DataTablesController;
use App\Http\Controllers\Web\AreaController as WebAreaController;
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
    // ROUTE for logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__.'/auth.php';
