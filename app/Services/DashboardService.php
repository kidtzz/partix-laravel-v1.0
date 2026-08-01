<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Setting;
use App\Models\BarangSupplier;
use App\Models\Penjualan;
use App\Models\ReturnTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getDashboardStats()
    {
        $stats = [
            'totalStockBarang' => 0,
            'totalPenjualanHariIni' => 0,
            'totalPendapatanHariIni' => 0,
            'totalPendapatanMingguIni' => 0,
            'totalPendapatanBulanIni' => 0,
            'totalPendapatanTahunIni' => 0,
            'pendapatanCashHariIni' => 0,
            'pendapatanCashMingguIni' => 0,
            'pendapatanCashBulanIni' => 0,
            'pendapatanCashTahunIni' => 0,
            'pendapatanTransferHariIni' => 0,
            'pendapatanTransferMingguIni' => 0,
            'pendapatanTransferBulanIni' => 0,
            'pendapatanTransferTahunIni' => 0,
            'pendapatanQRISHariIni' => 0,
            'pendapatanQRISMingguIni' => 0,
            'pendapatanQRISBulanIni' => 0,
            'pendapatanQRISTahunIni' => 0,
            'totalPotonganHariIni' => 0,
            'totalPotonganMingguIni' => 0,
            'totalPotonganBulanIni' => 0,
            'totalPotonganTahunIni' => 0,
            'totalRefundHariIni' => 0,
            'totalRefundMingguIni' => 0,
            'totalRefundBulanIni' => 0,
            'totalRefundTahunIni' => 0,
            'dailySales' => [],
            'notifikasiStockMinimum' => []
        ];

        // Global minimum stok
        $setting = Setting::where('key', 'MINIMUM_STOK')->first();
        $globalMinStok = $setting ? (int)$setting->value : 5;

        // Hitung stok
        $barangs = Barang::where('status_barang', 'Aktif')->get();
        $bsList = BarangSupplier::where('status', 'Aktif')->get()->groupBy('barang_id');

        foreach ($barangs as $b) {
            $relasi = $bsList->get($b->id, collect([]));
            if ($relasi->isNotEmpty()) {
                $stokSaatIni = $relasi->sum('stok_saat_ini');
                $stats['totalStockBarang'] += $stokSaatIni;

                if ($stokSaatIni <= $globalMinStok) {
                    $stats['notifikasiStockMinimum'][] = [
                        'id_barang' => $b->kode_barang,
                        'nama_barang' => $b->nama_barang,
                        'stok_saat_ini' => $stokSaatIni,
                        'minimum_stock' => $globalMinStok,
                        'satuan' => 'PCS'
                    ];
                }
            }
        }

        $now = Carbon::now('Asia/Jakarta');
        $todayStr = $now->format('Y-m-d');
        
        $startOfWeek = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        $penjualans = Penjualan::where('status_transaksi', 'Selesai')->get();
        foreach ($penjualans as $tx) {
            $txDateObj = Carbon::parse($tx->created_at)->timezone('Asia/Jakarta');
            $txDateStr = $txDateObj->format('Y-m-d');
            $potongan = (float)$tx->potongan;
            $tTotal = (float)$tx->total;
            $metode = strtolower($tx->metode_pembayaran);

            if ($txDateStr === $todayStr) {
                $stats['totalPenjualanHariIni']++;
                $stats['totalPendapatanHariIni'] += $tTotal;
                $stats['totalPotonganHariIni'] += $potongan;
                if ($metode === 'cash') $stats['pendapatanCashHariIni'] += $tTotal;
                elseif ($metode === 'transfer') $stats['pendapatanTransferHariIni'] += $tTotal;
                elseif ($metode === 'qris') $stats['pendapatanQRISHariIni'] += $tTotal;
            }

            if ($txDateObj >= $startOfWeek) {
                $stats['totalPendapatanMingguIni'] += $tTotal;
                $stats['totalPotonganMingguIni'] += $potongan;
                if ($metode === 'cash') $stats['pendapatanCashMingguIni'] += $tTotal;
                elseif ($metode === 'transfer') $stats['pendapatanTransferMingguIni'] += $tTotal;
                elseif ($metode === 'qris') $stats['pendapatanQRISMingguIni'] += $tTotal;
            }

            if ($txDateObj >= $startOfMonth) {
                $stats['totalPendapatanBulanIni'] += $tTotal;
                $stats['totalPotonganBulanIni'] += $potongan;
                if ($metode === 'cash') $stats['pendapatanCashBulanIni'] += $tTotal;
                elseif ($metode === 'transfer') $stats['pendapatanTransferBulanIni'] += $tTotal;
                elseif ($metode === 'qris') $stats['pendapatanQRISBulanIni'] += $tTotal;
            }

            if ($txDateObj >= $startOfYear) {
                $stats['totalPendapatanTahunIni'] += $tTotal;
                $stats['totalPotonganTahunIni'] += $potongan;
                if ($metode === 'cash') $stats['pendapatanCashTahunIni'] += $tTotal;
                elseif ($metode === 'transfer') $stats['pendapatanTransferTahunIni'] += $tTotal;
                elseif ($metode === 'qris') $stats['pendapatanQRISTahunIni'] += $tTotal;
            }

            if (!isset($stats['dailySales'][$txDateStr])) {
                $stats['dailySales'][$txDateStr] = 0;
            }
            $stats['dailySales'][$txDateStr] += $tTotal;
        }

        $returns = ReturnTransaction::where('status', 'Selesai')->get();
        foreach ($returns as $r) {
            $retDateObj = Carbon::parse($r->created_at)->timezone('Asia/Jakarta');
            $retDateStr = $retDateObj->format('Y-m-d');
            $selisih = (float)$r->selisih_harga;

            if ($selisih < 0) {
                $refundAmount = abs($selisih);
                if ($retDateStr === $todayStr) $stats['totalRefundHariIni'] += $refundAmount;
                if ($retDateObj >= $startOfWeek) $stats['totalRefundMingguIni'] += $refundAmount;
                if ($retDateObj >= $startOfMonth) $stats['totalRefundBulanIni'] += $refundAmount;
                if ($retDateObj >= $startOfYear) $stats['totalRefundTahunIni'] += $refundAmount;
            }
        }

        return $stats;
    }
}
