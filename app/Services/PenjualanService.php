<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\BarangSupplier;
use App\Models\StockMovement;
use App\Models\ProfilToko;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class PenjualanService
{
    public function scanBarcodePenjualan($barcode)
    {
        $b = Barang::where('status_barang', 'Aktif')
            ->where(function($q) use ($barcode) {
                $q->where('barcode1', $barcode)
                  ->orWhere('barcode2', $barcode);
            })->first();

        if (!$b) {
            throw new Exception("Barang dengan barcode $barcode tidak ditemukan atau tidak aktif.");
        }

        $barangService = new BarangService();
        $diskon = $barangService->getPengaturanDiskon();
        
        $relasi = BarangSupplier::where('barang_id', $b->id)->where('status', 'Aktif')->get();
        if ($relasi->isEmpty()) {
            throw new Exception("Barang tidak memiliki stok aktif.");
        }
        $stok = $relasi->sum('stok_saat_ini');
        
        $h = \App\Models\Harga::where('barang_id', $b->id)->where('status_harga', 'Aktif')->first();
        if (!$h) {
            throw new Exception("Barang tidak memiliki harga aktif.");
        }
        $regPrice = (float)$h->harga_regular;

        $mappedHarga = [
            "Regular" => $regPrice,
            "Member" => floor(($regPrice * (1 - ($diskon['DISKON_MEMBER'] / 100))) / 100) * 100,
            "Langganan" => floor(($regPrice * (1 - ($diskon['DISKON_LANGGANAN'] / 100))) / 100) * 100,
            "Bengkel" => floor(($regPrice * (1 - ($diskon['DISKON_BENGKEL'] / 100))) / 100) * 100,
            "Teman" => floor(($regPrice * (1 - ($diskon['DISKON_TEMAN'] / 100))) / 100) * 100,
            "Grosir" => floor(($regPrice * (1 - ($diskon['DISKON_GROSIR'] / 100))) / 100) * 100,
        ];
        $mappedHarga["Bengkel / Reseller"] = $mappedHarga["Bengkel"];
        $mappedHarga["Teman / Kenalan"] = $mappedHarga["Teman"];
        $mappedHarga["Grosir / VIP"] = $mappedHarga["Grosir"];

        return [
            'id_barang' => $b->kode_barang,
            'nama_barang' => $b->nama_barang,
            'barcode1' => $b->barcode1,
            'barcode2' => $b->barcode2,
            'stok_saat_ini' => $stok,
            'harga' => $mappedHarga
        ];
    }

    public function simpanTransaksi($cartItems, $tipeHarga, $metodeBayar, $detailBayar, $uangDiterima, $status, $existingInvoiceNo, $potonganPenjualan)
    {
        DB::beginTransaction();
        try {
            $noInvoice = $existingInvoiceNo;
            if (!$noInvoice) {
                $todayStr = date('Ymd');
                $latest = Penjualan::where('no_invoice', 'like', "INV-{$todayStr}-%")->orderBy('no_invoice', 'desc')->first();
                if ($latest) {
                    $lastCount = (int) substr($latest->no_invoice, -4);
                    $count = $lastCount + 1;
                } else {
                    $count = 1;
                }
                $noInvoice = "INV-" . $todayStr . "-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $subtotal = 0;
            foreach ($cartItems as $item) {
                $subtotal += $item['qty'] * $item['harga'][$tipeHarga];
            }
            
            $total = $subtotal - $potonganPenjualan;
            $kembalian = $uangDiterima - $total;

            $penjualan = Penjualan::create([
                'no_invoice' => $noInvoice,
                'user_id' => Auth::id() ?? 1,
                'kategori_customer' => $tipeHarga,
                'subtotal' => $subtotal,
                'potongan' => $potonganPenjualan,
                'total' => $total,
                'metode_pembayaran' => $metodeBayar,
                'detail_pembayaran' => json_encode($detailBayar),
                'kembalian' => $kembalian,
                'status_transaksi' => $status
            ]);

            foreach ($cartItems as $item) {
                $b = Barang::where('kode_barang', $item['id_barang'])->first();
                if (!$b) throw new Exception("Barang {$item['id_barang']} tidak ditemukan.");

                $hargaSatuan = $item['harga'][$tipeHarga];
                $qty = $item['qty'];

                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $b->id,
                    'qty' => $qty,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $qty * $hargaSatuan
                ]);

                $this->kurangiStok($b->id, $qty, $noInvoice);
            }

            LogService::log('CREATE', 'Penjualan', "Transaksi sukses: $noInvoice, Total: $total");
            DB::commit();

            return [
                'noInvoice' => $noInvoice,
                'kembalian' => $kembalian,
                'status' => 'success'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function kurangiStok($barangId, $qtySisa, $noInvoice)
    {
        $suppliers = BarangSupplier::where('barang_id', $barangId)
            ->where('status', 'Aktif')
            ->where('stok_saat_ini', '>', 0)
            ->orderByDesc('is_utama')
            ->orderBy('created_at')
            ->get();

        foreach ($suppliers as $sup) {
            if ($qtySisa <= 0) break;

            $kurang = min($sup->stok_saat_ini, $qtySisa);
            $sup->stok_saat_ini -= $kurang;
            $sup->save();

            StockMovement::create([
                'barang_id' => $barangId,
                'supplier_id' => $sup->supplier_id,
                'user_id' => Auth::id() ?? 1,
                'tipe_pergerakan' => 'Keluar',
                'qty_box' => 0,
                'qty_pcs' => $kurang,
                'alasan_perubahan' => "Penjualan ($noInvoice)",
            ]);

            $qtySisa -= $kurang;
        }

        if ($qtySisa > 0) {
            throw new Exception("Stok tidak mencukupi untuk barang ID: $barangId");
        }
    }

    public function getDaftarTransaksi()
    {
        return Penjualan::with('user')->orderBy('created_at', 'desc')->get()->map(function($p) {
            return [
                'no_invoice' => $p->no_invoice,
                'tanggal' => $p->created_at->format('Y-m-d H:i:s'),
                'kasir' => $p->user ? $p->user->name : 'Unknown',
                'tipe_harga' => $p->kategori_customer,
                'total' => $p->total,
                'metode_bayar' => $p->metode_pembayaran,
                'status' => $p->status_transaksi
            ];
        })->toArray();
    }

    public function cetakInvoice($noInvoice)
    {
        $p = Penjualan::with(['user', 'details.barang'])->where('no_invoice', $noInvoice)->first();
        if (!$p) throw new Exception("Invoice $noInvoice tidak ditemukan.");

        $items = $p->details->map(function($d) {
            return [
                'nama_barang' => $d->barang->nama_barang,
                'qty' => $d->qty,
                'harga_satuan' => $d->harga_satuan,
                'subtotal' => $d->subtotal
            ];
        });
        
        $toko = ProfilToko::first();

        return [
            'no_invoice' => $p->no_invoice,
            'tanggal' => $p->created_at->format('Y-m-d H:i:s'),
            'kasir' => $p->user ? $p->user->name : 'Unknown',
            'tipe_harga' => $p->kategori_customer,
            'subtotal' => $p->subtotal,
            'potongan' => $p->potongan,
            'total' => $p->total,
            'uang_diterima' => $p->kembalian + $p->total,
            'kembalian' => $p->kembalian,
            'metode_bayar' => $p->metode_pembayaran,
            'items' => $items->toArray(),
            'toko' => $toko ? $toko->toArray() : ['nama_toko' => 'Toko Default', 'alamat' => '', 'telepon' => '']
        ];
    }
}
