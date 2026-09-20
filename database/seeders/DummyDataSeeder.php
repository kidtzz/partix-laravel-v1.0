<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Supplier;
use App\Models\SystemLog;
use App\Models\LogActivity;
use App\Models\Barang;
use App\Models\BarangSupplier;
use App\Models\Harga;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\StockMovement;
use App\Models\ReturnTransaction;
use App\Models\ReturnDetail;
use App\Models\BarangReturn;
use App\Models\ReturnSupplier;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ==========================
        // 1. SEED USERS
        // ==========================
        echo "Generating 100 Dummy Users...\n";
        DB::beginTransaction();
        try {
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $kasirRole = Role::firstOrCreate(['name' => 'kasir']);
            $password = Hash::make('password123');

            for ($i = 1; $i <= 100; $i++) {
                $roleName = $faker->randomElement(['admin', 'kasir']);
                $username = strtolower($faker->unique()->firstName . rand(100, 999));
                
                $user = User::create([
                    'name' => $faker->name,
                    'username' => $username,
                    'email' => $username . '@example.com',
                    'password' => $password,
                    'status' => $faker->randomElement(['Aktif', 'Aktif', 'Non Aktif']),
                    'keterangan' => 'Karyawan ' . ($roleName == 'admin' ? 'Pusat' : 'Cabang'),
                    'email_verified_at' => now(),
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now')
                ]);
                $user->assignRole($roleName);
            }
            DB::commit();
            echo "100 Dummy Users created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating Users: " . $e->getMessage() . "\n";
        }

        // ==========================
        // 2. SEED SUPPLIERS & SYSTEM LOGS
        // ==========================
        echo "Generating 100 Suppliers...\n";
        DB::beginTransaction();
        try {
            for ($i = 1; $i <= 100; $i++) {
                Supplier::create([
                    'kode_supplier' => 'SUP-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999),
                    'nama_supplier' => $faker->company,
                    'pic' => json_encode([
                        'nama' => $faker->name,
                        'nomor_hp' => $faker->phoneNumber
                    ]),
                    'nomor_hp' => $faker->phoneNumber,
                    'email' => $faker->companyEmail,
                    'status_supplier' => $faker->randomElement(['Aktif', 'Aktif', 'Non Aktif'])
                ]);
            }
            DB::commit();
            echo "100 Suppliers created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating Suppliers: " . $e->getMessage() . "\n";
        }

        echo "Generating 1000 SystemLogs...\n";
        DB::beginTransaction();
        try {
            for ($i = 0; $i < 1000; $i++) {
                SystemLog::create([
                    'level' => $faker->randomElement(['info', 'warning', 'error', 'debug']),
                    'message' => $faker->sentence(),
                    'context' => json_encode(['ip' => $faker->ipv4, 'data' => $faker->word]),
                    'user_agent' => $faker->userAgent,
                    'url' => $faker->url,
                    'user' => 'System',
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                ]);
            }
            DB::commit();
            echo "1000 SystemLogs created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating SystemLogs: " . $e->getMessage() . "\n";
        }

        $user = User::first();
        if ($user) {
            echo "Generating 2000 Extra LogActivities...\n";
            DB::beginTransaction();
            try {
                for ($i = 0; $i < 2000; $i++) {
                    LogActivity::create([
                        'user_id' => $user->id,
                        'action' => $faker->randomElement(['Login', 'Logout', 'Update Profile', 'Delete Barang', 'Print Invoice', 'Export Excel']),
                        'module' => $faker->randomElement(['Dashboard', 'Laporan', 'Pengaturan', 'User Management', 'Penjualan']),
                        'details' => $faker->sentence(),
                        'created_at' => $faker->dateTimeBetween('-2 months', 'now')
                    ]);
                }
                DB::commit();
                echo "2000 Extra LogActivities created successfully!\n";
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Error generating extra LogActivity: " . $e->getMessage() . "\n";
            }
        }

        // ==========================
        // 3. SEED BARANG & HARGA & STOK
        // ==========================
        $kategoriList = ['Pelumas', 'Ban & Velg', 'Kelistrikan', 'Mesin', 'Body Part', 'Aksesoris'];
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
                
                $b = Barang::create([
                    'kode_barang' => $kode,
                    'nama_barang' => $faker->words(3, true) . ' ' . $kategori,
                    'barcode1' => $faker->unique()->ean13,
                    'kategori' => $kategori,
                    'status_barang' => 'Aktif',
                    'lokasi_rak' => 'A' . rand(1, 9),
                    'gambar' => null 
                ]);

                $modal = rand(10, 500) * 1000; 
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
            echo "Error generating Barangs: " . $e->getMessage() . "\n";
        }

        // ==========================
        // 4. SEED TRANSAKSI PENJUALAN
        // ==========================
        $barangs = Barang::all();
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
                $bayar = ceil($total / 50000) * 50000; 
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

        echo "Generating 1000 LogActivities...\n";
        DB::beginTransaction();
        try {
            for ($i = 0; $i < 1000; $i++) {
                LogActivity::create([
                    'user_id' => $user->id,
                    'action' => $faker->randomElement(['Login', 'Create', 'Update', 'Delete', 'Checkout']),
                    'module' => $faker->randomElement(['Auth', 'Master Barang', 'Penjualan', 'Retur', 'Supplier']),
                    'details' => $faker->sentence(),
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                ]);
            }
            DB::commit();
            echo "1000 LogActivity created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating Logs: " . $e->getMessage() . "\n";
        }

        if ($supplier) {
            echo "Generating 1000 StockMovements...\n";
            DB::beginTransaction();
            try {
                for ($i = 0; $i < 1000; $i++) {
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
                echo "1000 StockMovement created successfully!\n";
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Error generating StockMovement: " . $e->getMessage() . "\n";
            }
        }

        // ==========================
        // 5. SEED RETUR
        // ==========================
        $penjualans = Penjualan::all();
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
                    'selisih_harga' => rand(-5, 5) * 10000, 
                    'alasan_return' => $faker->sentence(),
                    'status' => $faker->randomElement(['Selesai', 'Menunggu']),
                    'created_at' => $faker->dateTimeBetween('-1 month', 'now')
                ]);

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
