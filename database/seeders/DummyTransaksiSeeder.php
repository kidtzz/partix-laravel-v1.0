<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\LogActivity;
use App\Models\StockMovement;

class DummyTransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $user = User::first();
        if (!$user) {
            echo "No user found! Please seed users first.\n";
            return;
        }

        $barangs = Barang::all();
        if ($barangs->isEmpty()) {
            echo "No barang found! Please seed barangs first.\n";
            return;
        }
        
        $supplier = Supplier::first();

        echo "Generating 100 Penjualan...\n";
        DB::beginTransaction();
        try {
            for ($i = 1; $i <= 100; $i++) {
                $subtotal = 0;
                $details = [];
                $numItems = rand(1, 5);
                
                for ($j = 0; $j < $numItems; $j++) {
                    $b = $barangs->random();
                    $qty = rand(1, 5);
                    $hargaSatuan = rand(50, 200) * 1000;
                    $stotal = $qty * $hargaSatuan;
                    $subtotal += $stotal;
                    
                    $details[] = [
                        'barang_id' => $b->id,
                        'qty' => $qty,
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $stotal
                    ];
                }

                $potongan = rand(0, 1) ? rand(5, 20) * 1000 : 0;
                $total = $subtotal - $potongan;
                $bayar = ceil($total / 50000) * 50000; // Pembulatan keatas kelipatan 50k
                if ($bayar < $total) $bayar = $total;
                $kembalian = $bayar - $total;

                $penjualan = Penjualan::create([
                    'no_invoice' => 'INV-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999),
                    'user_id' => $user->id,
                    'kategori_customer' => $faker->randomElement(['Regular', 'Member', 'VIP']),
                    'subtotal' => $subtotal,
                    'potongan' => $potongan,
                    'total' => $total,
                    'metode_pembayaran' => $faker->randomElement(['Tunai', 'Transfer', 'Qris']),
                    'detail_pembayaran' => 'Lunas',
                    'kembalian' => $kembalian,
                    'status_transaksi' => 'Selesai',
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                ]);

                foreach ($details as $d) {
                    PenjualanDetail::create([
                        'penjualan_id' => $penjualan->id,
                        'barang_id' => $d['barang_id'],
                        'qty' => $d['qty'],
                        'harga_satuan' => $d['harga_satuan'],
                        'subtotal' => $d['subtotal']
                    ]);
                }
            }
            DB::commit();
            echo "100 Penjualan created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating Penjualan: " . $e->getMessage() . "\n";
        }

        echo "Generating 100 LogActivities...\n";
        DB::beginTransaction();
        try {
            for ($i = 0; $i < 100; $i++) {
                LogActivity::create([
                    'user_id' => $user->id,
                    'action' => $faker->randomElement(['Login', 'Create', 'Update', 'Delete', 'Checkout']),
                    'module' => $faker->randomElement(['Auth', 'Master Barang', 'Penjualan', 'Retur', 'Supplier']),
                    'details' => $faker->sentence(),
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                ]);
            }
            DB::commit();
            echo "100 LogActivity created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating Logs: " . $e->getMessage() . "\n";
        }

        if ($supplier) {
            echo "Generating 100 StockMovements...\n";
            DB::beginTransaction();
            try {
                for ($i = 0; $i < 100; $i++) {
                    $b = $barangs->random();
                    $isMasuk = rand(0, 1) == 1;
                    StockMovement::create([
                        'barang_id' => $b->id,
                        'supplier_id' => $isMasuk ? $supplier->id : null,
                        'user_id' => $user->id,
                        'tipe_pergerakan' => $isMasuk ? 'Masuk' : 'Keluar',
                        'qty_box' => rand(0, 2),
                        'qty_pcs' => rand(1, 50),
                        'harga_beli' => $isMasuk ? rand(10, 100) * 1000 : null,
                        'nomor_invoice_supplier' => $isMasuk ? 'SUP-' . rand(1000, 9999) : null,
                        'alasan_perubahan' => $isMasuk ? 'Restock barang' : 'Penjualan / Rusak',
                        'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                    ]);
                }
                DB::commit();
                echo "100 StockMovement created successfully!\n";
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Error generating StockMovement: " . $e->getMessage() . "\n";
            }
        }
    }
}
