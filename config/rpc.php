<?php

return [
    'routes' => [
        // Public / Kasir / Restocker (POS & Basic Operations)
        'scanBarcodePenjualan' => [\App\Services\PenjualanService::class, 'scanBarcodePenjualan', ['Kasir', 'Admin']],
        'simpanTransaksi' => [\App\Services\PenjualanService::class, 'simpanTransaksi', ['Kasir', 'Admin']],
        'getDaftarTransaksi' => [\App\Services\PenjualanService::class, 'getDaftarTransaksi', ['Kasir', 'Admin']],
        'cetakInvoice' => [\App\Services\PenjualanService::class, 'cetakInvoice', ['Kasir', 'Admin']],
        'getBarangUntukPOS' => [\App\Services\BarangService::class, 'getBarangUntukPOS', ['Kasir', 'Admin']],
        
        // Return Module
        'getDaftarReturLengkap' => [\App\Services\ReturnService::class, 'getDaftarReturLengkap', ['Kasir', 'Restocker', 'Admin']],
        'generatePDFReturBase64' => [\App\Services\ReturnService::class, 'generatePDFReturBase64', ['Kasir', 'Restocker', 'Admin']],
        'verifikasiInvoice' => [\App\Services\ReturnService::class, 'verifikasiInvoice', ['Kasir', 'Admin']],
        'cariBarangAktif' => [\App\Services\ReturnService::class, 'cariBarangAktif', ['Kasir', 'Restocker', 'Admin']],
        'prosesReturn' => [\App\Services\ReturnService::class, 'prosesReturn', ['Kasir', 'Admin']],
        'getListBarangReturn' => [\App\Services\ReturnService::class, 'getListBarangReturn', ['Kasir', 'Restocker', 'Admin']],
        'getHistoriReturSupplier' => [\App\Services\ReturnService::class, 'getHistoriReturSupplier', ['Restocker', 'Admin']],
        'prosesReturSupplier' => [\App\Services\ReturnService::class, 'prosesReturSupplier', ['Restocker', 'Admin']],
        
        // Stock / Inventory
        'getStockList' => [\App\Services\BarangService::class, 'getStockList', ['Restocker', 'Admin']],
        'scanBarcodeStock' => [\App\Services\BarangService::class, 'scanBarcodeStock', ['Restocker', 'Admin']],
        'getHistoriBarang' => [\App\Services\BarangService::class, 'getHistoriBarang', ['Restocker', 'Admin']],
        'updateStokBarang' => [\App\Services\BarangService::class, 'updateStokBarang', ['Restocker', 'Admin']],
        'inputBarangMasuk' => [\App\Services\BarangService::class, 'inputBarangMasuk', ['Restocker', 'Admin']],

        // Admin Only (Dashboard & Management)
        'getDashboardStats' => [\App\Services\DashboardService::class, 'getDashboardStats', ['Admin']],
        'getSemuaSupplier' => [\App\Services\SupplierService::class, 'getSemuaSupplier', ['Restocker', 'Admin']],
        'getSuppliers' => [\App\Services\SupplierService::class, 'getSemuaSupplier', ['Restocker', 'Admin']], // alias
        'tambahSupplier' => [\App\Services\SupplierService::class, 'tambahSupplier', ['Restocker', 'Admin']],
        'updateSupplier' => [\App\Services\SupplierService::class, 'updateSupplier', ['Restocker', 'Admin']],
        'hapusSupplier' => [\App\Services\SupplierService::class, 'hapusSupplier', ['Restocker', 'Admin']],
        
        'getPengaturanDiskon' => [\App\Services\BarangService::class, 'getPengaturanDiskon', ['Kasir', 'Restocker', 'Admin']],
        'updatePengaturanDiskon' => [\App\Services\BarangService::class, 'updatePengaturanDiskon', ['Admin']],
        
        'getHargaMasterList' => [\App\Services\BarangService::class, 'getHargaMasterList', ['Admin']],
        'getSemuaBarangAdmin' => [\App\Services\BarangService::class, 'getSemuaBarangAdmin', ['Restocker', 'Admin']],
        'tambahMasterBarang' => [\App\Services\BarangService::class, 'tambahMasterBarang', ['Restocker', 'Admin']],
        'updateMasterBarang' => [\App\Services\BarangService::class, 'updateMasterBarang', ['Restocker', 'Admin']],
        'ubahStatusBarang' => [\App\Services\BarangService::class, 'ubahStatusBarang', ['Restocker', 'Admin']],
        'updateStatusBarang' => [\App\Services\BarangService::class, 'updateStatusBarang', ['Restocker', 'Admin']],
        'hapusMasterBarang' => [\App\Services\BarangService::class, 'hapusMasterBarang', ['Restocker', 'Admin']],
        'hapusTotalKecualiMaster' => [\App\Services\BarangService::class, 'hapusTotalKecualiMaster', ['Admin']],
        'getBarangSupplier' => [\App\Services\BarangService::class, 'getBarangSupplier', ['Restocker', 'Admin']],
        'tambahBarangSupplier' => [\App\Services\BarangService::class, 'tambahBarangSupplier', ['Restocker', 'Admin']],
        'updateBarangSupplier' => [\App\Services\BarangService::class, 'updateBarangSupplier', ['Restocker', 'Admin']],
        'hapusBarangSupplier' => [\App\Services\BarangService::class, 'hapusBarangSupplier', ['Restocker', 'Admin']],
        'updateHargaJual' => [\App\Services\BarangService::class, 'updateHargaJual', ['Admin']],
        
        // Log & Audit (Admin)
        'getLogActivityAdmin' => [\App\Services\LogService::class, 'getLogActivityAdmin', ['Admin']],
        'getSystemLogs' => [\App\Services\LogService::class, 'getSystemLogs', ['Admin']],
        'logSystemEvent' => [\App\Services\LogService::class, 'logSystemEvent', ['Kasir', 'Restocker', 'Admin']],
        
        // User Management (Admin)
        'getSemuaUser' => [\App\Services\UserService::class, 'getSemuaUser', ['Admin']],
        'tambahUser' => [\App\Services\UserService::class, 'tambahUser', ['Admin']],
        'updateUser' => [\App\Services\UserService::class, 'updateUser', ['Admin']],
        'hapusUser' => [\App\Services\UserService::class, 'hapusUser', ['Admin']],
    ],
];
