<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Barang;
use App\Models\BarangSupplier;
use App\Models\Harga;
use App\Models\Supplier;

class DummyBarangSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $kategoriList = ['Pelumas', 'Ban & Velg', 'Kelistrikan', 'Mesin', 'Body Part', 'Aksesoris'];
        
        // Buat dummy supplier jika belum ada
        if (Supplier::count() == 0) {
            Supplier::create([
                'nama_supplier' => 'PT Sparepart Indonesia',
                'kontak' => '081234567890',
                'alamat' => 'Jakarta Raya'
            ]);
        }
        
        $supplier = Supplier::first();
        $tipeHargaList = ["Regular", "Bengkel / Reseller", "Grosir / VIP", "Teman / Kenalan"];

        $last = Barang::orderBy('id', 'desc')->first();
        $num = $last ? intval(str_replace('BRG-', '', $last->kode_barang)) : 0;

        echo "Generating 100 dummy barangs...\n";

        DB::beginTransaction();
        try {
            for ($i = 1; $i <= 100; $i++) {
                $num++;
                $kode = 'BRG-' . str_pad($num, 3, '0', STR_PAD_LEFT);
                $kategori = $faker->randomElement($kategoriList);
                
                // Create Barang
                $b = Barang::create([
                    'kode_barang' => $kode,
                    'nama_barang' => $faker->words(3, true) . ' ' . $kategori,
                    'barcode1' => $faker->unique()->ean13,
                    'kategori' => $kategori,
                    'status_barang' => 'Aktif',
                    'lokasi_rak' => 'A' . rand(1, 9),
                    'gambar' => null // Optional, if you want local image upload simulation, leave null or provide a placeholder URL.
                ]);

                // Create Barang Supplier (Stock)
                $modal = rand(10, 500) * 1000; // 10k - 500k
                BarangSupplier::create([
                    'barang_id' => $b->id,
                    'supplier_id' => $supplier->id,
                    'stok_saat_ini' => rand(10, 100),
                    'harga_beli' => $modal,
                    'satuan' => 'Pcs',
                    'isi_per_box' => 1,
                    'minimum_stok' => 5,
                    'diskon_persen' => 0,
                    'tanggal_masuk' => now(),
                    'status' => 'Aktif',
                    'is_utama' => true
                ]);

                // Create Harga
                Harga::create([
                    'barang_id' => $b->id,
                    'harga_regular' => $modal * 1.5,
                    'harga_langganan' => $modal * 1.2,
                    'harga_teman' => $modal * 1.1,
                    'status_harga' => 'Aktif'
                ]);
            }
            DB::commit();
            echo "100 dummy barangs created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
