<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Penjualan;
use App\Models\ReturnTransaction;
use App\Models\ReturnDetail;
use App\Models\BarangReturn;
use App\Models\ReturnSupplier;

class DummyReturSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $user = User::first();
        if (!$user) {
            echo "No user found!\n";
            return;
        }

        $barangs = Barang::all();
        $penjualans = Penjualan::all();
        $supplier = Supplier::first();

        if ($barangs->isEmpty() || $penjualans->isEmpty()) {
            echo "Please seed Barangs and Penjualans first.\n";
            return;
        }

        echo "Generating 100 Return Transactions...\n";
        DB::beginTransaction();
        try {
            for ($i = 1; $i <= 100; $i++) {
                $penjualan = $penjualans->random();
                
                $retur = ReturnTransaction::create([
                    'no_return' => 'RET-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999),
                    'no_invoice' => $penjualan->no_invoice,
                    'user_id' => $user->id,
                    'jenis_return' => $faker->randomElement(['Tukar Barang', 'Uang Kembali', 'Klaim Garansi']),
                    'selisih_harga' => rand(-5, 5) * 10000, // -50k to 50k
                    'alasan_return' => $faker->sentence(),
                    'status' => $faker->randomElement(['Selesai', 'Menunggu']),
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                ]);

                // 1-2 details per retur
                $numDetails = rand(1, 2);
                for ($j = 0; $j < $numDetails; $j++) {
                    $barangDireturn = $barangs->random();
                    $barangPengganti = rand(0,1) ? $barangs->random() : $barangDireturn;
                    $qty = rand(1, 3);
                    
                    ReturnDetail::create([
                        'return_transaction_id' => $retur->id,
                        'barang_direturn_id' => $barangDireturn->id,
                        'qty_direturn' => $qty,
                        'barang_pengganti_id' => $retur->jenis_return == 'Tukar Barang' ? $barangPengganti->id : null,
                        'qty_pengganti' => $retur->jenis_return == 'Tukar Barang' ? $qty : 0
                    ]);
                }
            }
            DB::commit();
            echo "100 Return Transactions created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating Return Transactions: " . $e->getMessage() . "\n";
        }

        echo "Generating 100 Barang Rusak (BarangReturn)...\n";
        DB::beginTransaction();
        try {
            for ($i = 0; $i < 100; $i++) {
                BarangReturn::create([
                    'no_invoice_asal' => $penjualans->random()->no_invoice,
                    'barang_id' => $barangs->random()->id,
                    'qty_rusak' => rand(1, 5),
                    'alasan' => $faker->randomElement(['Pecah', 'Cacat Pabrik', 'Kadaluarsa', 'Lecet']),
                    'user_id' => $user->id,
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                ]);
            }
            DB::commit();
            echo "100 BarangReturn created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating BarangReturn: " . $e->getMessage() . "\n";
        }

        if ($supplier) {
            echo "Generating 100 Return to Supplier...\n";
            DB::beginTransaction();
            try {
                for ($i = 0; $i < 100; $i++) {
                    ReturnSupplier::create([
                        'barang_id' => $barangs->random()->id,
                        'supplier_id' => $supplier->id,
                        'qty_retur' => rand(5, 20),
                        'harga_beli' => rand(50, 150) * 1000,
                        'no_invoice_supplier' => 'INV-SUP-' . rand(1000, 9999),
                        'user_id' => $user->id,
                        'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                    ]);
                }
                DB::commit();
                echo "100 ReturnSupplier created successfully!\n";
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Error generating ReturnSupplier: " . $e->getMessage() . "\n";
            }
        }
    }
}
