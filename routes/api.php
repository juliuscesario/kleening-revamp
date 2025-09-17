<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\ServiceOrderController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\WorkPhotoController;

Route::post('/login', [AuthController::class, 'login']);

// Route yang butuh login
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    //SET ROUTES
    Route::apiResource('areas', AreaController::class);
    Route::apiResource('service-categories', ServiceCategoryController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('customers', CustomerController::class);

    // Route untuk Address yang terikat dengan Customer
    Route::get('/customers/{customer}/addresses', [AddressController::class, 'indexByCustomer']);
    Route::post('/customers/{customer}/addresses', [AddressController::class, 'storeForCustomer']);

    // Route untuk Show, Update, Delete Address secara individual
    Route::apiResource('addresses', AddressController::class)->only(['show', 'update', 'destroy']);

    //Route Staff
    Route::apiResource('staff', StaffController::class);

    //Route Service Order
    Route::apiResource('service-orders', ServiceOrderController::class);

    // Route khusus untuk membuat Invoice dari Service Order
    Route::post('/service-orders/{serviceOrder}/invoice', [InvoiceController::class, 'storeFromServiceOrder']);

    // Route standar untuk mengelola Invoice (melihat, update status, hapus)
    Route::apiResource('invoices', InvoiceController::class);

    // Route untuk mengelola foto di bawah Service Order
    Route::post('/service-orders/{serviceOrder}/photos', [WorkPhotoController::class, 'store']);
    Route::get('/service-orders/{serviceOrder}/photos', [WorkPhotoController::class, 'index']);
    Route::delete('/photos/{workPhoto}', [WorkPhotoController::class, 'destroy']); // Untuk hapus foto individual

});
