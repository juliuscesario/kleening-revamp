<?php

use App\Http\Controllers\Web\AreaController as WebAreaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
    Route::resource('areas', WebAreaController::class);

    // ROUTE BARU UNTUK DATATABLES
    Route::get('data/areas', function(Request $request) {
        // Ambil token dari session user yang login
        $token = $request->user()->createToken('data-tables')->plainTextToken;

        // Teruskan request ke API kita, lengkap dengan token
        $response = Http::withToken($token)
                        ->withHeaders(['Accept' => 'application/json'])
                        ->get(config('app.url').'/api/areas', $request->query());
        
        // Hapus token setelah digunakan
        $request->user()->tokens()->delete();

        return $response->json();
    })->name('data.areas');
});

require __DIR__.'/auth.php';
