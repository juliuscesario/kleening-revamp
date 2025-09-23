<?php

use App\Http\Controllers\Web\DataTablesController;
use App\Http\Controllers\Web\AreaController as WebAreaController;
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
    Route::resource('areas', WebAreaController::class);
    Route::get('data/areas', [DataTablesController::class, 'areas'])->name('data.areas');
});

require __DIR__.'/auth.php';
