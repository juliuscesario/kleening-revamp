<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\CustomerController;



//SET ROUTES
Route::apiResource('areas', AreaController::class);
Route::apiResource('service-categories', ServiceCategoryController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('customers', CustomerController::class);

