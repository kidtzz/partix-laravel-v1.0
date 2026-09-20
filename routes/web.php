<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

require __DIR__.'/auth.php';

// Route Rahasia untuk deploy ke cPanel tanpa SSH
Route::get('/partix-secret-migrate-77', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return nl2br("Migrate Status:\n" . \Illuminate\Support\Facades\Artisan::output());
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/partix-secret-clear-cache-77', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return "Cache, Config, dan Route berhasil dibersihkan!";
});

Route::get('/partix-secret-storage-link-77', function () {
    try {
        $targetFolder = storage_path('app/public');
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        
        // Remove existing public/storage if it's a broken symlink or folder
        $linkFolder = public_path('storage');
        if (file_exists($linkFolder) || is_link($linkFolder)) {
            if (PHP_OS_FAMILY === 'Windows') {
                exec('rmdir /s /q "' . $linkFolder . '"');
            } else {
                exec('rm -rf "' . $linkFolder . '"');
            }
        }

        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return "Storage Link berhasil dibuat!";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
