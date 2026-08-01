<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturnTransaction;
use App\Models\ReturnDetail;
use App\Models\BarangReturn;
use App\Models\BarangSupplier;
use App\Models\StockMovement;
use App\Models\ReturnSupplier;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ReturnService
{
    public function getDaftarReturLengkap()
    {
        return ReturnTransaction::with(['user', 'details.barangDireturn', 'details.barangPengganti'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($rt) {
                return [
                    'no_return' => $rt->no_return,
                    'no_invoice' => $rt->no_invoice,
                    'tanggal' => $rt->created_at->format('Y-m-d H:i:s'),
                    'kasir' => $rt->user ? $rt->user->name : 'Unknown',
                    'jenis_return' => $rt->jenis_return,
                    'selisih_harga' => $rt->selisih_harga,
                    'alasan' => $rt->alasan_return,
                    'status' => $rt->status,
                    'items' => $rt->details->map(function($d) {
                        return [
                            'nama_barang_kembali' => $d->barangDireturn ? $d->barangDireturn->nama_barang : '',
                            'qty_kembali' => $d->qty_direturn,
                            'nama_barang_pengganti' => $d->barangPengganti ? $d->barangPengganti->nama_barang : '',
                            'qty_pengganti' => $d->qty_pengganti
                        ];
                    })->toArray()
                ];
            })->toArray();
    }

    public function generatePDFReturBase64($noReturn)
    {
        return "JVBERi0xLjQKJcOkw7zDtsOfCjIgMCBvYmoKPDwvTGVuZ3RoIDMgMCBSPj4Kc3RyZWFtCkJ1aWxkIGluIFBERiBnZW5lcmF0aW9uIGlzIG5vdCBmdWxseSBzdXBwb3J0ZWQgeWV0LgplbmRzdHJlYW0KZW5kb2JqCgo=";
    }

    public function verifikasiInvoice($noInvoice)
    {
        $penjualan = Penjualan::with(['details.barang', 'user'])->where('no_invoice', $noInvoice)->first();
        if (!$penjualan) {
            throw new Exception("Invoice $noInvoice tidak ditemukan.");
        }

        $items = $penjualan->details->map(function($d) {
            return [
                'id_barang' => $d->barang ? $d->barang->kode_barang : '',
                'nama_barang' => $d->barang ? $d->barang->nama_barang : '',
                'qty' => $d->qty,
                'harga_satuan' => $d->harga_satuan,
                'subtotal' => $d->subtotal
            ];
        });

        $is_returned = \App\Models\ReturnTransaction::where('no_invoice', $noInvoice)->exists();

        return [
            'header' => [
                'no_invoice' => $penjualan->no_invoice,
                'tanggal' => $penjualan->created_at->format('Y-m-d H:i:s'),
                'kategori_customer' => $penjualan->kategori_customer,
                'kasir' => $penjualan->user ? $penjualan->user->name : 'Unknown',
                'total' => $penjualan->total,
                'is_returned' => $is_returned
            ],
            'detail' => $items->toArray()
        ];
    }

    public function cariBarangAktif($query)
    {
        return Barang::where('status_barang', 'Aktif')
            ->where(function($q) use ($query) {
                $q->where('nama_barang', 'like', "%$query%")
                  ->orWhere('kode_barang', 'like', "%$query%")
                  ->orWhere('barcode1', 'like', "%$query%")
                  ->orWhere('barcode2', 'like', "%$query%");
            })
            ->limit(10)
            ->get()
            ->map(function($b) {
                $h = \App\Models\Harga::where('barang_id', $b->id)->where('status_harga', 'Aktif')->first();
                $hargaReg = $h ? $h->harga_regular : 0;
                return [
                    'id_barang' => $b->kode_barang,
                    'nama_barang' => $b->nama_barang,
                    'harga_jual' => $hargaReg,
                    'stok_saat_ini' => \App\Models\BarangSupplier::where('barang_id', $b->id)->where('status', 'Aktif')->sum('stok_saat_ini') ?? 0
                ];
            })->toArray();
    }

    public function prosesReturn($noInvoice, $items, $jenisGlobal, $selisihBayar)
    {
        DB::beginTransaction();
        try {
            $today = date('Ymd');
            $lastReturn = ReturnTransaction::where('no_return', 'like', "RET-{$today}-%")->orderBy('id', 'desc')->first();
            $count = 1;
            if ($lastReturn) {
                $lastCount = (int) substr($lastReturn->no_return, -4);
                $count = $lastCount + 1;
            }
            $noReturn = "RET-" . $today . "-" . str_pad($count, 4, '0', STR_PAD_LEFT);

            $rt = ReturnTransaction::create([
                'no_return' => $noReturn,
                'no_invoice' => $noInvoice,
                'user_id' => Auth::id() ?? 1,
                'jenis_return' => $jenisGlobal,
                'selisih_harga' => $selisihBayar,
                'status' => 'Selesai'
            ]);

            foreach ($items as $item) {
                $bKembali = Barang::where('kode_barang', $item['id_barang_direturn'])->first();
                if (!$bKembali) throw new Exception("Barang kembali {$item['id_barang_direturn']} tidak ditemukan.");

                $bPengganti = null;
                if (!empty($item['id_barang_pengganti'])) {
                    $bPengganti = Barang::where('kode_barang', $item['id_barang_pengganti'])->first();
                    if (!$bPengganti) throw new Exception("Barang pengganti {$item['id_barang_pengganti']} tidak ditemukan.");
                }

                ReturnDetail::create([
                    'return_transaction_id' => $rt->id,
                    'barang_direturn_id' => $bKembali->id,
                    'qty_direturn' => $item['qty_return'],
                    'barang_pengganti_id' => $bPengganti ? $bPengganti->id : null,
                    'qty_pengganti' => $item['qty_pengganti']
                ]);
                
                if (isset($item['alasan_return']) && in_array($item['alasan_return'], ['Cacat Pabrik', 'Rusak'])) {
                    BarangReturn::create([
                        'no_invoice_asal' => $noInvoice,
                        'barang_id' => $bKembali->id,
                        'qty_rusak' => $item['qty_return'],
                        'alasan' => $item['alasan_return'] . ' dari Pelanggan (Retur: '.$noReturn.')',
                        'user_id' => Auth::id() ?? 1
                    ]);
                } else {
                    // Do not add stock back, as per user request.
                }

                if ($bPengganti && $item['qty_pengganti'] > 0) {
                    $this->kurangiStok($bPengganti->id, $item['qty_pengganti'], "Retur Pengganti ($noReturn)");
                }
            }

            LogService::log('CREATE', 'Retur', "Retur Pelanggan sukses: $noReturn");
            DB::commit();

            return [
                'noReturn' => $noReturn,
                'status' => 'success'
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function kurangiStok($barangId, $qtySisa, $keterangan)
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
                'qty_pcs' => $kurang,
                'alasan_perubahan' => $keterangan,
            ]);

            $qtySisa -= $kurang;
        }

        if ($qtySisa > 0) {
            throw new Exception("Stok pengganti tidak mencukupi untuk ID: $barangId");
        }
    }

    public function getListBarangReturn()
    {
        return BarangReturn::with(['barang', 'user'])->orderBy('created_at', 'desc')->get()->map(function($br) {
            return [
                'id_return' => $br->id,
                'tanggal' => $br->created_at->format('Y-m-d H:i:s'),
                'no_invoice' => $br->no_invoice_asal,
                'id_barang' => $br->barang ? $br->barang->kode_barang : '',
                'nama_barang' => $br->barang ? $br->barang->nama_barang : '',
                'qty_rusak' => $br->qty_rusak,
                'alasan' => $br->alasan,
                'kasir' => $br->user ? $br->user->name : ''
            ];
        })->toArray();
    }

    public function getHistoriReturSupplier()
    {
        return ReturnSupplier::with(['barang', 'supplier', 'user'])->orderBy('created_at', 'desc')->get()->map(function($rs) {
            return [
                'id_return_supplier' => 'RS-' . str_pad($rs->id, 4, '0', STR_PAD_LEFT),
                'tanggal_retur' => $rs->created_at->format('Y-m-d H:i:s'),
                'id_barang' => $rs->barang ? $rs->barang->kode_barang : '',
                'nama_barang' => $rs->barang ? $rs->barang->nama_barang : '',
                'nama_supplier' => $rs->supplier ? $rs->supplier->nama_supplier : '',
                'qty_retur' => $rs->qty_retur,
                'harga_beli' => $rs->harga_beli,
                'no_invoice_supplier' => $rs->no_invoice_supplier,
                'user' => $rs->user ? $rs->user->name : ''
            ];
        })->toArray();
    }

    public function prosesReturSupplier($payload)
    {
        DB::beginTransaction();
        try {
            $br = null;
            $b = null;
            if (!empty($payload['id_barang_return'])) {
                $br = \App\Models\BarangReturn::find($payload['id_barang_return']);
                if ($br) {
                    $b = \App\Models\Barang::find($br->barang_id);
                }
            }
            
            $s = \App\Models\Supplier::where('kode_supplier', $payload['id_supplier'])->first();
            
            if (!$b || !$s) throw new Exception("Data Barang Karantina atau Supplier tidak ditemukan.");

            \App\Models\ReturnSupplier::create([
                'barang_id' => $b->id,
                'supplier_id' => $s->id,
                'qty_retur' => $payload['qty_retur'],
                'harga_beli' => $payload['harga_beli'] ?? 0,
                'no_invoice_supplier' => $payload['no_invoice_supplier'] ?? '-',
                'user_id' => Auth::id() ?? 1
            ]);

            if ($br) {
                if ($br->qty_rusak <= $payload['qty_retur']) {
                    $br->delete();
                } else {
                    $br->qty_rusak -= $payload['qty_retur'];
                    $br->save();
                }
            }

            LogService::log('CREATE', 'Retur Supplier', "Retur Gudang ke Supplier sukses: {$b->kode_barang}");
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
