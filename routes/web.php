<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

require __DIR__.'/auth.php';

// Route Rahasia untuk deploy ke cPanel tanpa SSH
Route::get('/partix-secret-migrate-77', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return "Database berhasil diupdate (Migrate Success)!";
});

Route::get('/partix-secret-clear-cache-77', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "Cache, Config, dan Route berhasil dibersihkan!";
});
