<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RpcController extends Controller
{
    public function handle(Request $request)
    {
        $method = $request->input('method');
        $args = $request->input('args', []);

        // Mapping method ke Service Class & Method
        $routes = [
            'getDashboardStats' => [\App\Services\DashboardService::class, 'getDashboardStats'],
            'getBarangUntukPOS' => [\App\Services\BarangService::class, 'getBarangUntukPOS'],
            'getStockList' => [\App\Services\BarangService::class, 'getStockList'],
            'getSemuaSupplier' => [\App\Services\SupplierService::class, 'getSemuaSupplier'],
            'tambahSupplier' => [\App\Services\SupplierService::class, 'tambahSupplier'],
            'updateSupplier' => [\App\Services\SupplierService::class, 'updateSupplier'],
            'hapusSupplier' => [\App\Services\SupplierService::class, 'hapusSupplier'],
            'getPengaturanDiskon' => [\App\Services\BarangService::class, 'getPengaturanDiskon'],
            'updatePengaturanDiskon' => [\App\Services\BarangService::class, 'updatePengaturanDiskon'],
            'getHargaMasterList' => [\App\Services\BarangService::class, 'getHargaMasterList'],
            
            // Phase 5: Inventory & Stock CRUD
            'getSemuaBarangAdmin' => [\App\Services\BarangService::class, 'getSemuaBarangAdmin'],
            'tambahMasterBarang' => [\App\Services\BarangService::class, 'tambahMasterBarang'],
            'updateMasterBarang' => [\App\Services\BarangService::class, 'updateMasterBarang'],
            'ubahStatusBarang' => [\App\Services\BarangService::class, 'ubahStatusBarang'],
            
            // Log & Audit
            'getLogActivityAdmin' => [\App\Services\LogService::class, 'getLogActivityAdmin'],
            'getSystemLogs' => [\App\Services\LogService::class, 'getSystemLogs'],
            'logSystemEvent' => [\App\Services\LogService::class, 'logSystemEvent'],
            'updateStatusBarang' => [\App\Services\BarangService::class, 'updateStatusBarang'],
            'hapusMasterBarang' => [\App\Services\BarangService::class, 'hapusMasterBarang'],
            'inputBarangMasuk' => [\App\Services\BarangService::class, 'inputBarangMasuk'],
            'updateStokBarang' => [\App\Services\BarangService::class, 'updateStokBarang'],
            'scanBarcodeStock' => [\App\Services\BarangService::class, 'scanBarcodeStock'],
            'getHistoriBarang' => [\App\Services\BarangService::class, 'getHistoriBarang'],
            'hapusTotalKecualiMaster' => [\App\Services\BarangService::class, 'hapusTotalKecualiMaster'],
            'getBarangSupplier' => [\App\Services\BarangService::class, 'getBarangSupplier'],
            'tambahBarangSupplier' => [\App\Services\BarangService::class, 'tambahBarangSupplier'],
            'updateBarangSupplier' => [\App\Services\BarangService::class, 'updateBarangSupplier'],
            'hapusBarangSupplier' => [\App\Services\BarangService::class, 'hapusBarangSupplier'],
            'updateHargaJual' => [\App\Services\BarangService::class, 'updateHargaJual'],
            
            // User Management
            'getSemuaUser' => [\App\Services\UserService::class, 'getSemuaUser'],
            'tambahUser' => [\App\Services\UserService::class, 'tambahUser'],
            'updateUser' => [\App\Services\UserService::class, 'updateUser'],
            'hapusUser' => [\App\Services\UserService::class, 'hapusUser'],

            // Penjualan / POS
            'scanBarcodePenjualan' => [\App\Services\PenjualanService::class, 'scanBarcodePenjualan'],
            'simpanTransaksi' => [\App\Services\PenjualanService::class, 'simpanTransaksi'],
            'getDaftarTransaksi' => [\App\Services\PenjualanService::class, 'getDaftarTransaksi'],
            'cetakInvoice' => [\App\Services\PenjualanService::class, 'cetakInvoice'],

            // Return Module
            'getDaftarReturLengkap' => [\App\Services\ReturnService::class, 'getDaftarReturLengkap'],
            'generatePDFReturBase64' => [\App\Services\ReturnService::class, 'generatePDFReturBase64'],
            'verifikasiInvoice' => [\App\Services\ReturnService::class, 'verifikasiInvoice'],
            'cariBarangAktif' => [\App\Services\ReturnService::class, 'cariBarangAktif'],
            'prosesReturn' => [\App\Services\ReturnService::class, 'prosesReturn'],
            'getListBarangReturn' => [\App\Services\ReturnService::class, 'getListBarangReturn'],
            'getHistoriReturSupplier' => [\App\Services\ReturnService::class, 'getHistoriReturSupplier'],
            'getSuppliers' => [\App\Services\SupplierService::class, 'getSemuaSupplier'], // alias to existing method
            'prosesReturSupplier' => [\App\Services\ReturnService::class, 'prosesReturSupplier'],
        ];

        if (!array_key_exists($method, $routes)) {
            return response()->json(['message' => "Method $method not implemented in backend."], 501);
        }

        try {
            $callable = $routes[$method];
            $class = new $callable[0]();
            // Call the service method with the provided arguments
            $result = call_user_func_array([$class, $callable[1]], $args);
            
            // The frontend expects the direct return value (e.g. array/object),
            // which will be automatically serialized to JSON by Laravel response()->json()
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("RPC Error [$method]: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
