<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangSupplier;
use App\Models\Harga;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class BarangService
{
    // ==========================================
    // PHASE 4 (READ)
    // ==========================================
    public function getPengaturanDiskon()
    {
        $settings = Setting::whereIn('key', [
            'DISKON_MEMBER', 'DISKON_LANGGANAN', 'DISKON_BENGKEL', 'DISKON_TEMAN', 'DISKON_GROSIR', 'MINIMUM_STOK'
        ])->get();

        $diskon = [
            'DISKON_MEMBER' => 0,
            'DISKON_LANGGANAN' => 0,
            'DISKON_BENGKEL' => 0,
            'DISKON_TEMAN' => 0,
            'DISKON_GROSIR' => 0,
            'MINIMUM_STOK' => 5,
        ];

        foreach ($settings as $s) {
            $diskon[$s->key] = (float)$s->value;
        }

        return $diskon;
    }

    public function getBarangUntukPOS()
    {
        $diskon = $this->getPengaturanDiskon();

        // Ambil barang, harga aktif, dan barang_supplier aktif
        $barangs = Barang::where('status_barang', 'Aktif')->get();
        $hargaList = Harga::where('status_harga', 'Aktif')->get()->keyBy('barang_id');
        $bsList = BarangSupplier::where('status', 'Aktif')->get()->groupBy('barang_id');

        $result = [];

        foreach ($barangs as $b) {
            $relasi = $bsList->get($b->id);
            $h = $hargaList->get($b->id);

            if ($relasi && $h) {
                $stok = $relasi->sum('stok_saat_ini');
                $maxHargaBeli = $relasi->max('harga_beli') ?: 0;
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

                $result[] = [
                    'id_barang' => $b->kode_barang,
                    'nama_barang' => $b->nama_barang,
                    'stok_saat_ini' => $stok,
                    'barcode1' => $b->barcode1,
                    'barcode2' => $b->barcode2,
                    'harga_modal' => $maxHargaBeli,
                    'harga' => $mappedHarga
                ];
            }
        }

        return $result;
    }

    public function getStockList()
    {
        $barangs = Barang::where('status_barang', 'Aktif')->get();
        $bsList = BarangSupplier::all()->groupBy('barang_id');
        $supplierList = Supplier::all()->keyBy('id');
        
        $result = [];
        foreach ($barangs as $b) {
            $relasi = $bsList->get($b->id, collect([]));
            if ($relasi->isEmpty() && $b->status_barang === 'Aktif') {
                continue; // skip if no supplier relation and active
            }

            $utama = $relasi->firstWhere('is_utama', true) ?? $relasi->first();

            $suppliersDetail = $relasi->map(function ($bs) use ($supplierList) {
                $sup = $supplierList->get($bs->supplier_id);
                return [
                    'id_barang_supplier' => $bs->kode_barang_supplier,
                    'id_supplier' => $sup ? $sup->kode_supplier : '',
                    'nama_supplier' => $sup ? $sup->nama_supplier : '',
                    'harga_beli' => (float)$bs->harga_beli,
                    'diskon_persen' => (float)$bs->diskon_persen,
                    'satuan' => $bs->satuan,
                    'isi_per_box' => (int)$bs->isi_per_box,
                    'kode_barang_supplier' => $bs->kode_barang_supplier,
                    'is_utama' => (bool)$bs->is_utama,
                    'status' => $bs->status
                ];
            })->toArray();

            $stok = $relasi->sum('stok_saat_ini');
            
            $result[] = [
                'id_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'barcode1' => $b->barcode1,
                'barcode2' => $b->barcode2,
                'stok_total' => $stok, // mapped to UI
                'stok_saat_ini' => $stok,
                'minimum_stok' => $utama ? $utama->minimum_stok : 0,
                'lokasi_rak' => $b->lokasi_rak,
                'satuan' => $utama ? $utama->satuan : 'PCS',
                'isi_per_box' => $utama ? $utama->isi_per_box : 1,
                'id_bs_utama' => $utama ? $utama->kode_barang_supplier : '',
                'status_stok' => $utama ? str_replace('Non Aktif', 'Nonaktif', $utama->status) : 'Aktif',
                'status_barang' => str_replace('Non Aktif', 'Nonaktif', $b->status_barang),
                'tanggal_masuk' => $relasi->max('tanggal_masuk'),
                'suppliers' => $suppliersDetail
            ];
        }
        return $result;
    }

    // ==========================================
    // PHASE 5 (WRITE & INVENTORY CRUD)
    // ==========================================
    
    public function getHargaMasterList()
    {
        $barangs = Barang::where('status_barang', 'Aktif')->get();
        $bsList = BarangSupplier::where('status', 'Aktif')->get()->groupBy('barang_id');
        $hargaList = Harga::get()->groupBy('barang_id');
        $diskon = $this->getPengaturanDiskon();

        return $barangs->filter(function ($b) use ($bsList) {
            return $bsList->has($b->id);
        })->map(function ($b) use ($bsList, $hargaList, $diskon) {
            $relasi = $bsList->get($b->id, collect([]));
            $stok = $relasi->sum('stok_saat_ini');
            $maxHargaBeli = $relasi->max('harga_beli') ?: 0;

            $itemPrices = $hargaList->get($b->id, collect([]));
            $h = $itemPrices->firstWhere('status_harga', 'Aktif') ?? $itemPrices->last();
            
            $regPrice = $h ? (float)$h->harga_regular : 0;
            $statusHarga = $h ? str_replace('Non Aktif', 'Nonaktif', $h->status_harga) : 'Nonaktif';

            $mappedHarga = [
                "Regular" => $regPrice,
                "Member" => floor(($regPrice * (1 - ($diskon['DISKON_MEMBER'] / 100))) / 100) * 100,
                "Langganan" => floor(($regPrice * (1 - ($diskon['DISKON_LANGGANAN'] / 100))) / 100) * 100,
                "Bengkel" => floor(($regPrice * (1 - ($diskon['DISKON_BENGKEL'] / 100))) / 100) * 100,
                "Teman" => floor(($regPrice * (1 - ($diskon['DISKON_TEMAN'] / 100))) / 100) * 100,
                "Grosir" => floor(($regPrice * (1 - ($diskon['DISKON_GROSIR'] / 100))) / 100) * 100
            ];

            return [
                'id_barang' => $b->kode_barang,
                'nama_barang' => $b->nama_barang,
                'barcode1' => $b->barcode1,
                'barcode2' => $b->barcode2,
                'barcode' => implode(', ', array_filter([$b->barcode1, $b->barcode2])),
                'harga_modal' => $maxHargaBeli,
                'harga' => $mappedHarga,
                'stok_saat_ini' => $stok,
                'status_harga' => $statusHarga,
                'status_barang' => str_replace('Non Aktif', 'Nonaktif', $b->status_barang)
            ];
        })->values()->toArray();
    }

    public function updatePengaturanDiskon($data)
    {
        $keysToUpdate = [];
        if (isset($data['DISKON_MEMBER'])) $keysToUpdate['DISKON_MEMBER'] = (float)$data['DISKON_MEMBER'];
        if (isset($data['DISKON_LANGGANAN'])) $keysToUpdate['DISKON_LANGGANAN'] = (float)$data['DISKON_LANGGANAN'];
        if (isset($data['DISKON_BENGKEL'])) $keysToUpdate['DISKON_BENGKEL'] = (float)$data['DISKON_BENGKEL'];
        if (isset($data['DISKON_TEMAN'])) $keysToUpdate['DISKON_TEMAN'] = (float)$data['DISKON_TEMAN'];
        if (isset($data['DISKON_GROSIR'])) $keysToUpdate['DISKON_GROSIR'] = (float)$data['DISKON_GROSIR'];
        if (isset($data['MINIMUM_STOK'])) $keysToUpdate['MINIMUM_STOK'] = (int)$data['MINIMUM_STOK'];

        foreach ($keysToUpdate as $key => $val) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string)$val]);
        }
        
        LogService::log('UPDATE', 'Pengaturan Global', "Update Diskon / Minimum Stok");
        return true;
    }

    private function getBarangByKode($kode)
    {
        $b = Barang::where('kode_barang', $kode)->first();
        if (!$b) throw new Exception("Barang tidak ditemukan: $kode");
        return $b;
    }

    public function getSemuaBarangAdmin()
    {
        $barangs = Barang::all();
        $hargaList = Harga::where('status_harga', 'Aktif')->get()->keyBy('barang_id');

        return $barangs->map(function ($b) use ($hargaList) {
            $h = $hargaList->get($b->id);
            return [
                'id_barang' => $b->kode_barang,
                'barcode1' => $b->barcode1 ?? '',
                'barcode2' => $b->barcode2 ?? '',
                'barcode' => implode(', ', array_filter([$b->barcode1, $b->barcode2])),
                'nama_barang' => $b->nama_barang,
                'lokasi_rak' => $b->lokasi_rak ?? '',
                'kategori' => $b->kategori ?? '',
                'status_barang' => str_replace('Non Aktif', 'Nonaktif', $b->status_barang),
                'harga' => [
                    'Regular' => $h ? (float)$h->harga_regular : 0,
                    'Langganan' => $h ? (float)$h->harga_langganan : 0,
                    'Teman' => $h ? (float)$h->harga_teman : 0,
                ]
            ];
        })->toArray();
    }

    private function generateKodeBarang()
    {
        $last = Barang::orderBy('id', 'desc')->first();
        if (!$last) return 'BRG-001';
        $num = (int)str_replace('BRG-', '', $last->kode_barang);
        return 'BRG-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public function tambahMasterBarang($data)
    {
        if (empty($data['nama_barang'])) throw new Exception("Nama barang wajib diisi.");

        $bc1 = trim($data['barcode1'] ?? '');
        $bc2 = trim($data['barcode2'] ?? '');

        if (!$bc1 && !empty($data['barcode'])) {
            $parts = explode(',', $data['barcode']);
            $bc1 = trim($parts[0] ?? '');
            $bc2 = trim($parts[1] ?? '');
        }

        if ($bc1 && Barang::where('barcode1', $bc1)->orWhere('barcode2', $bc1)->exists()) {
            throw new Exception("Barcode 1 ($bc1) sudah digunakan.");
        }
        if ($bc2 && Barang::where('barcode1', $bc2)->orWhere('barcode2', $bc2)->exists()) {
            throw new Exception("Barcode 2 ($bc2) sudah digunakan.");
        }

        $kode = $this->generateKodeBarang();

        DB::beginTransaction();
        try {
            $b = Barang::create([
                'kode_barang' => $kode,
                'barcode1' => $bc1 ?: null,
                'barcode2' => $bc2 ?: null,
                'nama_barang' => $data['nama_barang'],
                'lokasi_rak' => $data['lokasi_rak'] ?? null,
                'kategori' => $data['kategori'] ?? null,
                'status_barang' => str_replace('Nonaktif', 'Non Aktif', $data['status_barang'] ?? 'Aktif')
            ]);

            LogService::log('CREATE', 'Master Barang', "Menambah barang baru: {$b->nama_barang} ($kode)");
            DB::commit();
            return $kode;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateMasterBarang($idBarang, $data)
    {
        $b = $this->getBarangByKode($idBarang);
        $oldStatus = $b->status_barang;

        $bc1 = trim($data['barcode1'] ?? '');
        $bc2 = trim($data['barcode2'] ?? '');

        if (!$bc1 && !empty($data['barcode'])) {
            $parts = explode(',', $data['barcode']);
            $bc1 = trim($parts[0] ?? '');
            $bc2 = trim($parts[1] ?? '');
        }

        DB::beginTransaction();
        try {
            $b->update([
                'nama_barang' => $data['nama_barang'],
                'barcode1' => $bc1 ?: null,
                'barcode2' => $bc2 ?: null,
                'lokasi_rak' => $data['lokasi_rak'] ?? $b->lokasi_rak,
                'kategori' => $data['kategori'] ?? $b->kategori,
                'status_barang' => str_replace('Nonaktif', 'Non Aktif', $data['status_barang'] ?? $b->status_barang)
            ]);

            $statusText = $oldStatus !== str_replace('Nonaktif', 'Non Aktif', $data['status_barang'] ?? $b->status_barang) ? " - Status: " . ($data['status_barang'] ?? $b->status_barang) : "";
            LogService::log('UPDATE', 'Master Barang', "Update barang: $idBarang$statusText");
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function ubahStatusBarang($idBarang, $status)
    {
        $b = $this->getBarangByKode($idBarang);
        $b->update(['status_barang' => str_replace('Nonaktif', 'Non Aktif', $status)]);
        LogService::log('UPDATE', 'Master Barang', "Ubah Status Barang: $idBarang menjadi $status");
        return true;
    }

    public function updateStatusBarang($idBarang, $statusBaru)
    {
        $b = $this->getBarangByKode($idBarang);
        DB::beginTransaction();
        try {
            $b->update(['status_barang' => str_replace('Nonaktif', 'Non Aktif', $statusBaru)]);
            BarangSupplier::where('barang_id', $b->id)->update(['status' => str_replace('Nonaktif', 'Non Aktif', $statusBaru)]);
            LogService::log('UPDATE', 'Status Barang', "Update status barang: $idBarang menjadi $statusBaru");
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function hapusMasterBarang($idBarang)
    {
        $b = $this->getBarangByKode($idBarang);
        DB::beginTransaction();
        try {
            $b->delete(); // cascades properly if set, otherwise need to manually delete
            LogService::log('DELETE', 'Master Barang', "Menghapus permanen barang: $idBarang");
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function hapusTotalKecualiMaster($idBarang)
    {
        $b = $this->getBarangByKode($idBarang);
        DB::beginTransaction();
        try {
            BarangSupplier::where('barang_id', $b->id)->delete();
            Harga::where('barang_id', $b->id)->delete();
            LogService::log('DELETE', 'Stok Barang', "Menghapus seluruh data stok dan harga untuk barang: $idBarang");
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateHargaJual($idBarang, $hargaRegular, $keteranganPerubahan = "", $statusHarga = "Aktif")
    {
        $b = $this->getBarangByKode($idBarang);
        
        DB::beginTransaction();
        try {
            Harga::where('barang_id', $b->id)->where('status_harga', 'Aktif')->update(['status_harga' => 'Non Aktif']);

            $diskon = $this->getPengaturanDiskon();
            
            Harga::create([
                'barang_id' => $b->id,
                'harga_regular' => $hargaRegular,
                'harga_langganan' => floor(($hargaRegular * (1 - ($diskon['DISKON_LANGGANAN'] / 100))) / 100) * 100,
                'harga_teman' => floor(($hargaRegular * (1 - ($diskon['DISKON_TEMAN'] / 100))) / 100) * 100,
                'status_harga' => str_replace('Nonaktif', 'Non Aktif', $statusHarga),
                'tanggal_berlaku' => now(),
                'keterangan_perubahan' => $keteranganPerubahan
            ]);

            LogService::log('UPDATE', 'Harga Jual', "Update harga untuk barang $idBarang");
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function inputBarangMasuk($idBarang, $idSupplier, $qtyBox, $isiPerBox, $hargaBeli, $tanggal, $diskonPersen = 0, $noInvoiceSupplier = "")
    {
        $b = $this->getBarangByKode($idBarang);
        $sup = Supplier::where('kode_supplier', $idSupplier)->first();
        if (!$sup) throw new Exception("Supplier tidak ditemukan.");

        DB::beginTransaction();
        try {
            $relation = BarangSupplier::where('barang_id', $b->id)->where('supplier_id', $sup->id)->where('status', 'Aktif')->first();
            
            $finalIsiPerBox = $relation ? $relation->isi_per_box : ((int)$isiPerBox ?: 1);
            $qtyPcs = $qtyBox * $finalIsiPerBox;

            StockMovement::create([
                'barang_id' => $b->id,
                'supplier_id' => $sup->id ?? null,
                'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                'tipe_pergerakan' => 'RESTOCK',
                'qty_box' => $qtyBox ?? 0,
                'qty_pcs' => $qtyPcs,
                'harga_beli' => $hargaBeli,
                'nomor_invoice_supplier' => $noInvoiceSupplier,
                'alasan_perubahan' => 'Barang masuk dari supplier ' . $noInvoiceSupplier
            ]);

            if (!$relation) {
                // Determine if this is the first supplier -> make it is_utama
                $isUtama = !BarangSupplier::where('barang_id', $b->id)->where('is_utama', true)->where('status', 'Aktif')->exists();
                
                $kodeBs = 'BS-' . strtoupper(uniqid());
                $relation = BarangSupplier::create([
                    'barang_id' => $b->id,
                    'supplier_id' => $sup->id,
                    'harga_beli' => $hargaBeli,
                    'diskon_persen' => $diskonPersen,
                    'satuan' => 'PCS',
                    'isi_per_box' => $finalIsiPerBox,
                    'stok_saat_ini' => $qtyPcs,
                    'minimum_stok' => 5, // default
                    'lokasi_rak' => '',
                    'kode_barang_supplier' => $kodeBs,
                    'is_utama' => $isUtama,
                    'status' => 'Aktif',
                    'tanggal_masuk' => Carbon::parse($tanggal)
                ]);
                $stokBaru = $qtyPcs;
            } else {
                $stokBaru = $relation->stok_saat_ini + $qtyPcs;
                $relation->update([
                    'stok_saat_ini' => $stokBaru,
                    'harga_beli' => $hargaBeli ?: $relation->harga_beli,
                    'diskon_persen' => $diskonPersen !== null ? $diskonPersen : $relation->diskon_persen,
                    'tanggal_masuk' => Carbon::parse($tanggal)
                ]);
            }

            LogService::log('CREATE', 'Stok Barang', "Barang Masuk: $idBarang, Qty: $qtyBox Box ($qtyPcs PCS)");
            DB::commit();
            return ['success' => true, 'stokBaru' => $stokBaru];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function scanBarcodeStock($barcode)
    {
        $barcode = trim(strtolower($barcode));
        $b = Barang::whereRaw('LOWER(barcode1) = ?', [$barcode])
            ->orWhereRaw('LOWER(barcode2) = ?', [$barcode])
            ->orWhereRaw('LOWER(kode_barang) = ?', [$barcode])
            ->first();
        
        if (!$b) throw new Exception("Barang dengan barcode $barcode tidak ditemukan.");
        
        LogService::log('READ', 'Scan Barcode', "Scan barcode: $barcode");
        
        return [
            'id_barang' => $b->kode_barang,
            'nama_barang' => $b->nama_barang,
            'barcode1' => $b->barcode1,
            'barcode2' => $b->barcode2,
            'lokasi_rak' => $b->lokasi_rak,
            'kategori' => $b->kategori,
        ];
    }

    public function updateStokBarang($idBarang, $data)
    {
        $b = $this->getBarangByKode($idBarang);
        
        DB::beginTransaction();
        try {
            $relasi = BarangSupplier::where('barang_id', $b->id)->get();
            if ($relasi->isEmpty()) throw new Exception("Barang ini belum memiliki supplier.");

            $utama = $relasi->firstWhere('is_utama', true) ?? $relasi->first();

            $updateData = [];
            if (isset($data['stok_saat_ini'])) $updateData['stok_saat_ini'] = (int)$data['stok_saat_ini'];
            if (isset($data['minimum_stok'])) $updateData['minimum_stok'] = (int)$data['minimum_stok'];
            if (isset($data['lokasi_rak'])) $updateData['lokasi_rak'] = $data['lokasi_rak'];
            if (isset($data['isi_per_box'])) $updateData['isi_per_box'] = (int)$data['isi_per_box'];
            if (isset($data['satuan'])) $updateData['satuan'] = $data['satuan'];

            $utama->update($updateData);

            if (isset($data['status'])) {
                BarangSupplier::where('barang_id', $b->id)->update(['status' => str_replace('Nonaktif', 'Non Aktif', $data['status'])]);
            }

            $user = auth('sanctum')->user();
            StockMovement::create([
                'barang_id' => $b->id,
                'supplier_id' => $utama->supplier_id,
                'user_id' => $user ? $user->id : 1,
                'tipe_pergerakan' => 'EDIT',
                'qty_box' => 0, 'qty_pcs' => 0, 
                'harga_beli' => $utama->harga_beli,
                'alasan_perubahan' => "Edit Manual: Stok " . ($updateData['stok_saat_ini'] ?? $utama->stok_saat_ini) . " PCS"
            ]);

            LogService::log('UPDATE', 'Stok Barang', "Update stok/min/lokasi barang $idBarang");
            DB::commit();
            return ['success' => true];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getHistoriBarang($idBarang)
    {
        $b = $this->getBarangByKode($idBarang);
        
        $movements = StockMovement::with('user')->where('barang_id', $b->id)->orderBy('created_at', 'desc')->get();
        $prices = Harga::where('barang_id', $b->id)->orderBy('tanggal_berlaku', 'desc')->get();

        $history = collect();

        foreach ($movements as $m) {
            $history->push([
                'tanggal' => $m->created_at->format('Y-m-d H:i:s'),
                'jenis' => 'Stok (' . $m->tipe_pergerakan . ')',
                'deskripsi' => ($m->qty_pcs > 0 || $m->qty_box > 0) ? ($m->qty_pcs > 0 ? $m->qty_pcs : $m->qty_box) . " Pcs - " . $m->alasan_perubahan : $m->alasan_perubahan,
                'user' => $m->user ? $m->user->username : 'Sistem'
            ]);
        }

        foreach ($prices as $h) {
            $history->push([
                'tanggal' => Carbon::parse($h->tanggal_berlaku)->format('Y-m-d H:i:s'),
                'jenis' => 'Harga',
                'deskripsi' => "Reg: Rp{$h->harga_regular}, Lgn: Rp{$h->harga_langganan}, Tmn: Rp{$h->harga_teman} ({$h->keterangan_perubahan})",
                'user' => 'Sistem' // can parse from keterangan if needed
            ]);
        }

        return $history->sortByDesc('tanggal')->values()->toArray();
    }
    
    // Fallback implementations for getBarangSupplier etc (since UI calls these via RPC)
    public function getBarangSupplier()
    {
        // For partial_stock.blade.php / partial_admin.blade.php
        $bsList = BarangSupplier::with(['barang', 'supplier'])->get();
        return $bsList->map(function($bs) {
            return [
                'id_barang_supplier' => $bs->kode_barang_supplier,
                'id_barang' => $bs->barang ? $bs->barang->kode_barang : '',
                'id_supplier' => $bs->supplier ? $bs->supplier->kode_supplier : '',
                'harga_beli' => $bs->harga_beli,
                'diskon_persen' => $bs->diskon_persen,
                'satuan' => $bs->satuan,
                'isi_per_box' => $bs->isi_per_box,
                'stok_saat_ini' => $bs->stok_saat_ini,
                'minimum_stok' => $bs->minimum_stok,
                'lokasi_rak' => $bs->lokasi_rak,
                'is_utama' => $bs->is_utama,
                'status' => str_replace('Non Aktif', 'Nonaktif', $bs->status),
                'tanggal_masuk' => $bs->tanggal_masuk
            ];
        })->toArray();
    }

    public function tambahBarangSupplier($payload)
    {
        $b = $this->getBarangByKode($payload['id_barang']);
        $sup = Supplier::where('kode_supplier', $payload['id_supplier'])->first();
        if (!$sup) throw new Exception("Supplier invalid.");

        $kodeBs = 'BS-' . strtoupper(uniqid());
        StockMovement::create([
            'barang_id' => $b->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
            'tipe_pergerakan' => 'PENYESUAIAN',
            'qty_box' => 0,
            'qty_pcs' => $payload['stok_saat_ini'] ?? 0,
            'harga_beli' => 0,
            'alasan_perubahan' => $payload['catatan_penyesuaian'] ?? 'Penambahan supplier baru'
        ]);
        
        $existingBs = BarangSupplier::where('barang_id', $b->id)->where('supplier_id', $sup->id)->first();
        if ($existingBs) {
            $existingBs->update([
                'harga_beli' => $payload['harga_beli'] ?? $existingBs->harga_beli,
                'diskon_persen' => $payload['diskon_persen'] ?? $existingBs->diskon_persen,
                'status' => 'Aktif'
            ]);
            return $existingBs->kode_barang_supplier;
        }

        BarangSupplier::create([
            'barang_id' => $b->id,
            'supplier_id' => $sup->id,
            'harga_beli' => $payload['harga_beli'] ?? 0,
            'diskon_persen' => $payload['diskon_persen'] ?? 0,
            'satuan' => $payload['satuan'] ?? 'PCS',
            'isi_per_box' => $payload['isi_per_box'] ?? 1,
            'stok_saat_ini' => $payload['stok_saat_ini'] ?? 0,
            'minimum_stok' => $payload['minimum_stok'] ?? 0,
            'lokasi_rak' => $payload['lokasi_rak'] ?? '',
            'kode_barang_supplier' => $kodeBs,
            'is_utama' => $payload['is_utama'] ?? false,
            'status' => str_replace('Nonaktif', 'Non Aktif', $payload['status'] ?? 'Aktif'),
            'tanggal_masuk' => !empty($payload['tanggal_masuk']) ? Carbon::parse($payload['tanggal_masuk']) : now()
        ]);
        return $kodeBs;
    }

    public function updateBarangSupplier($idBs, $payload)
    {
        $bs = BarangSupplier::where('kode_barang_supplier', $idBs)->first();
        if (!$bs) throw new Exception("Barang Supplier $idBs tidak ditemukan.");
        
        $updateData = [];
        if (isset($payload['harga_beli'])) $updateData['harga_beli'] = $payload['harga_beli'];
        if (isset($payload['diskon_persen'])) $updateData['diskon_persen'] = $payload['diskon_persen'];
        if (isset($payload['satuan'])) $updateData['satuan'] = $payload['satuan'];
        if (isset($payload['isi_per_box'])) $updateData['isi_per_box'] = $payload['isi_per_box'];
        if (isset($payload['stok_saat_ini'])) $updateData['stok_saat_ini'] = $payload['stok_saat_ini'];
        if (isset($payload['minimum_stok'])) $updateData['minimum_stok'] = $payload['minimum_stok'];
        if (isset($payload['lokasi_rak'])) $updateData['lokasi_rak'] = $payload['lokasi_rak'];
        if (isset($payload['is_utama'])) $updateData['is_utama'] = $payload['is_utama'];
        if (isset($payload['status'])) $updateData['status'] = str_replace('Nonaktif', 'Non Aktif', $payload['status']);

        $bs->update($updateData);
        return true;
    }

    public function hapusBarangSupplier($idBs)
    {
        BarangSupplier::where('kode_barang_supplier', $idBs)->delete();
        return true;
    }
}
