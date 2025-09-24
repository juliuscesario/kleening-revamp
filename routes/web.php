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
    $user = Auth::user();
    $todayServiceOrders = collect();
    $tomorrowServiceOrders = collect();
    $pastServiceOrders = collect();
    $cancelledServiceOrders = collect();
    $allBookedServiceOrders = collect();
    $allProsesServiceOrders = collect();

    if ($user->role === 'staff' && $user->staff) {
        $allServiceOrders = App\Models\ServiceOrder::whereHas('staff', function ($q) use ($user) {
            $q->where('staff.id', $user->staff->id);
        })
        ->with(['customer', 'address', 'items.service', 'staff'])
        ->orderBy('work_date', 'asc')
        ->get();

        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        foreach ($allServiceOrders as $so) {
            $workDate = Carbon::parse($so->work_date);

            if ($so->status === 'cancelled') {
                $cancelledServiceOrders->push($so);
            } else {
                if ($so->status === 'booked') {
                    $allBookedServiceOrders->push($so);
                } elseif ($so->status === 'proses') {
                    $allProsesServiceOrders->push($so);
                }

                if ($workDate->isSameDay($today)) {
                    $todayServiceOrders->push($so);
                } elseif ($workDate->isSameDay($tomorrow)) {
                    $tomorrowServiceOrders->push($so);
                } elseif ($workDate->lt($today)) { // Past dates
                    $pastServiceOrders->push($so);
                }
            }
        }
    }

    return view('dashboard', compact('todayServiceOrders', 'tomorrowServiceOrders', 'pastServiceOrders', 'cancelledServiceOrders', 'allBookedServiceOrders', 'allProsesServiceOrders'));
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