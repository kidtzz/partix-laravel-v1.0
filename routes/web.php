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
            
            // Hapus symlink lama jika dia symlink (jangan hapus folder kalau sudah folder beneran)
            if (is_link($cpanelLink)) {
                if (PHP_OS_FAMILY === 'Windows') exec('rmdir /s /q "' . $cpanelLink . '"');
                else exec('rm -rf "' . $cpanelLink . '"');
            }
            
            // Bikin folder sungguhan (Bukan Symlink!) biar nggak kena blokir cPanel
            if (!file_exists($cpanelLink)) {
                mkdir($cpanelLink, 0777, true);
                
                // Copy isi dari storage/app/public ke public_html/storage
                if (PHP_OS_FAMILY === 'Windows') {
                    exec('xcopy "' . $targetFolder . '" "' . $cpanelLink . '" /E /I /Y');
                } else {
                    exec('cp -r "' . $targetFolder . '/." "' . $cpanelLink . '/"');
                }

                $messages[] = "Khusus cPanel: Folder public_html/storage (Folder Asli) berhasil dibuat & isi disalin! Bypass Symlink Block aktif.";
            } else {
                $messages[] = "Khusus cPanel: Folder public_html/storage sudah ada sebagai folder asli. Aman!";
            }
        } else {
            $messages[] = "Folder public_html tidak ditemukan (diabaikan).";
        }

        return nl2br(implode("\n", $messages));
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});
