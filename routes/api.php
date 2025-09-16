<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\StaffController;

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


