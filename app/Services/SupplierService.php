<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    public function getSemuaSupplier()
    {
        return Supplier::all()->map(function ($sup) {
            $pics = [];
            if ($sup->pic) {
                $decoded = json_decode($sup->pic, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    if (isset($decoded['nama']) || isset($decoded['hp'])) {
                        $pics = [$decoded]; // Wrap single object in array
                    } else {
                        $pics = $decoded;
                    }
                } else {
                    $pics = [['nama' => $sup->pic, 'hp' => $sup->nomor_hp]];
                }
            } else if ($sup->nomor_hp) {
                $pics = [['nama' => '', 'hp' => $sup->nomor_hp]];
            }
            return [
                'id_supplier' => $sup->kode_supplier,
                'nama_supplier' => $sup->nama_supplier,
                'pic' => $sup->pic,
                'nomor_hp' => $sup->nomor_hp,
                'email' => $sup->email,
                'status_supplier' => str_replace('Non Aktif', 'Nonaktif', $sup->status_supplier),
                'pics' => $pics
            ];
        })->toArray();
    }

    public function tambahSupplier($data)
    {
        $last = Supplier::orderBy('id', 'desc')->first();
        $num = $last ? (int)str_replace('SUP-', '', $last->kode_supplier) : 0;
        $newId = 'SUP-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);

        $pics = $data['pics'] ?? [];
        $picVal = $data['pic'] ?? '';
        $hpVal = $data['nomor_hp'] ?? '';

        if (!empty($pics)) {
            $validPics = array_filter($pics, fn($p) => !empty($p['nama']) || !empty($p['hp']));
            if (!empty($validPics)) {
                $picVal = json_encode(array_values($validPics));
                $hpVal = implode(', ', array_filter(array_column($validPics, 'hp')));
            }
        }

        $s = Supplier::create([
            'kode_supplier' => $newId,
            'nama_supplier' => $data['nama_supplier'],
            'pic' => $picVal,
            'nomor_hp' => $hpVal,
            'email' => $data['email'] ?? '',
            'status_supplier' => str_replace('Nonaktif', 'Non Aktif', $data['status_supplier'] ?? 'Aktif')
        ]);

        LogService::log('CREATE', 'Master Supplier', "Menambah supplier baru: {$s->nama_supplier} ({$newId})");
        return $newId;
    }

    public function updateSupplier($idSupplier, $data)
    {
        $s = Supplier::where('kode_supplier', $idSupplier)->first();
        if (!$s) throw new \Exception("Supplier invalid");

        $pics = $data['pics'] ?? [];
        $picVal = $data['pic'] ?? '';
        $hpVal = $data['nomor_hp'] ?? '';

        if (!empty($pics)) {
            $validPics = array_filter($pics, fn($p) => !empty($p['nama']) || !empty($p['hp']));
            if (!empty($validPics)) {
                $picVal = json_encode(array_values($validPics));
                $hpVal = implode(', ', array_filter(array_column($validPics, 'hp')));
            }
        }

        $s->update([
            'nama_supplier' => $data['nama_supplier'],
            'pic' => $picVal,
            'nomor_hp' => $hpVal,
            'email' => $data['email'] ?? $s->email,
            'status_supplier' => str_replace('Nonaktif', 'Non Aktif', $data['status_supplier'] ?? $s->status_supplier)
        ]);

        LogService::log('UPDATE', 'Master Supplier', "Update supplier: $idSupplier - Status: {$s->status_supplier}");
        return true;
    }

    public function hapusSupplier($idSupplier)
    {
        $s = Supplier::where('kode_supplier', $idSupplier)->first();
        if (!$s) throw new \Exception("Supplier invalid");
        
        $nama = $s->nama_supplier;
        $s->delete();
        
        LogService::log('DELETE', 'Master Supplier', "Menghapus supplier permanen: $nama ($idSupplier)");
        return true;
    }
}

