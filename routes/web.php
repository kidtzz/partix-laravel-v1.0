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
        
        $messages = [];
        
        // 1. Link standar Laravel (partix/public/storage)
        $linkFolder = public_path('storage');
        if (file_exists($linkFolder) || is_link($linkFolder)) {
            if (PHP_OS_FAMILY === 'Windows') exec('rmdir /s /q "' . $linkFolder . '"');
            else exec('rm -rf "' . $linkFolder . '"');
        }
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        $messages[] = "Standar Laravel (public/storage) berhasil dibuat.";
        
        // 2. Link khusus cPanel (public_html/storage)
        $cpanelPublicHtml = base_path('../public_html');
        if (is_dir($cpanelPublicHtml)) {
            $cpanelLink = $cpanelPublicHtml . '/storage';
            
            // Hapus jika sudah ada
            if (file_exists($cpanelLink) || is_link($cpanelLink)) {
                if (PHP_OS_FAMILY === 'Windows') exec('rmdir /s /q "' . $cpanelLink . '"');
                else exec('rm -rf "' . $cpanelLink . '"');
            }
            
            // Bikin symlink manual
            if (symlink($targetFolder, $cpanelLink)) {
                $messages[] = "Khusus cPanel (public_html/storage) berhasil dibuat!";
            } else {
                $messages[] = "Gagal membuat symlink di public_html. Pastikan symlink diizinkan di hosting Anda.";
            }
        } else {
            $messages[] = "Folder public_html tidak ditemukan (diabaikan).";
        }

        return nl2br(implode("\n", $messages));
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
