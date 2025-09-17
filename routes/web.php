<?php

use Illuminate\Support\Facades\Route;

// Halaman utama bisa kita arahkan ke login jika belum masuk
Route::get('/', function () {
    return view('auth.login');
});

// Route untuk menampilkan dashboard setelah login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    // Route-route admin lainnya akan ada di sini
});
