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
Route::middleware(['auth:sanctum', 'role:owner,admin,co_owner,staff'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    //SET ROUTES
    Route::apiResource('areas', AreaController::class);
    Route::apiResource('service-categories', ServiceCategoryController::class);
    Route::apiResource('machine-categories', \App\Http\Controllers\Api\MachineCategoryController::class);
    Route::get('machines/next-code', [\App\Http\Controllers\Api\MachineController::class, 'nextCode']);
    Route::apiResource('machines', \App\Http\Controllers\Api\MachineController::class);

    // Machine Attendance
    Route::get('machine-attendance/status', [\App\Http\Controllers\Api\MachineAttendanceController::class, 'status']);
    Route::get('machine-attendance/available-machines', [\App\Http\Controllers\Api\MachineAttendanceController::class, 'availableMachines']);
    Route::post('machine-attendance/pergi', [\App\Http\Controllers\Api\MachineAttendanceController::class, 'pergi']);
    Route::post('machine-attendance/{id}/pulang', [\App\Http\Controllers\Api\MachineAttendanceController::class, 'pulang']);

    // Machine Attendance Management (Owner/Co-owner)
    Route::get('machine-attendances', [\App\Http\Controllers\Api\MachineAttendanceManageController::class, 'index']);
    Route::get('machine-attendances/{id}', [\App\Http\Controllers\Api\MachineAttendanceManageController::class, 'show']);
    Route::put('machine-attendances/{id}', [\App\Http\Controllers\Api\MachineAttendanceManageController::class, 'update']);
    Route::post('machine-attendances/{id}/force-close', [\App\Http\Controllers\Api\MachineAttendanceManageController::class, 'forceClose']);
    Route::delete('machine-attendances/{id}', [\App\Http\Controllers\Api\MachineAttendanceManageController::class, 'destroy']);

    Route::apiResource('services', ServiceController::class);
    Route::apiResource('customers', CustomerController::class);

    // Route untuk Address yang terikat dengan Customer
    Route::get('/customers/{customer}/addresses', [AddressController::class, 'indexByCustomer']);
    Route::post('/customers/{customer}/addresses', [AddressController::class, 'storeForCustomer']);

    // Route untuk Show, Update, Delete Address secara individual
    Route::apiResource('addresses', AddressController::class)->only(['show', 'update', 'destroy']);

    //Route Staff
    Route::apiResource('staff', StaffController::class)->except(['destroy']);
    Route::post('staff/{staff}/resign', [StaffController::class, 'resign'])->name('staff.resign');

    //Route Service Order
    Route::apiResource('service-orders', ServiceOrderController::class);
    Route::post('/service-orders/{serviceOrder}/start-work', [ServiceOrderController::class, 'startWork']);
    Route::post('/service-orders/{serviceOrder}/upload-work-proof', [ServiceOrderController::class, 'uploadWorkProof']);
    Route::post('/service-orders/{serviceOrder}/complete-work', [ServiceOrderController::class, 'completeWork']);
    Route::post('/service-orders/{serviceOrder}/submit-signature', [ServiceOrderController::class, 'submitCustomerSignature']);

    // Route khusus untuk membuat Invoice dari Service Order
    Route::post('/service-orders/{serviceOrder}/invoice', [InvoiceController::class, 'storeFromServiceOrder']);

    // Route standar untuk mengelola Invoice (melihat, update status, hapus)
    Route::apiResource('invoices', InvoiceController::class)->except(['store']);

    // Route untuk mengelola foto di bawah Service Order
    Route::post('/service-orders/{serviceOrder}/photos', [WorkPhotoController::class, 'store']);
    Route::get('/service-orders/{serviceOrder}/photos', [WorkPhotoController::class, 'index']);
    Route::delete('/service-orders/{serviceOrder}/photos/{workPhoto}', [WorkPhotoController::class, 'destroy'])
        ->middleware('role:owner,co_owner,admin');
    Route::patch('/service-orders/{serviceOrder}/status', [ServiceOrderController::class, 'updateStatus']);

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/mark-as-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
});
