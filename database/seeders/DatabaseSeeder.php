<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProfilToko;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\BarangSupplier;
use App\Models\Harga;
use App\Models\StockMovement;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturnTransaction;
use App\Models\ReturnDetail;
use App\Models\BarangReturn;
use App\Models\ReturnSupplier;
use App\Models\LogActivity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Kasir']);
        Role::create(['name' => 'Restocker']);

        // 1. Users (Aktif & Nonaktif)
        $users = [
            ['username' => 'admin', 'password' => Hash::make('123456'), 'name' => 'Administrator', 'status' => 'Aktif', 'role' => 'Admin'],
            ['username' => 'kasir1', 'password' => Hash::make('123456'), 'name' => 'Budi Kasir', 'status' => 'Aktif', 'role' => 'Kasir'],
            ['username' => 'kasir2', 'password' => Hash::make('123456'), 'name' => 'Siti Kasir', 'status' => 'Aktif', 'role' => 'Kasir'],
            ['username' => 'kasir3', 'password' => Hash::make('123456'), 'name' => 'Agus Kasir (Resign)', 'status' => 'Non Aktif', 'role' => 'Kasir'],
            ['username' => 'restocker1', 'password' => Hash::make('123456'), 'name' => 'Andi Gudang', 'status' => 'Aktif', 'role' => 'Restocker'],
            ['username' => 'restocker2', 'password' => Hash::make('123456'), 'name' => 'Rina Gudang', 'status' => 'Non Aktif', 'role' => 'Restocker'],
        ];

        foreach ($users as $u) {
            $user = User::create(['username' => $u['username'], 'password' => $u['password'], 'name' => $u['name'], 'status' => $u['status']]);
            $user->assignRole($u['role']);
        }

        // 2. Profil Toko
        $profils = [
            ['kode_profil' => 'PROF-01', 'nama_toko' => 'Bengkel Partix Motor', 'logo_toko' => '', 'alamat_toko' => 'Jl. Raya Bogor No 10', 'nomor_telepon' => '081234567890', 'footer_invoice' => 'Terima Kasih atas Kunjungan Anda'],
        ];
        foreach ($profils as $profil) { ProfilToko::create($profil); }

        // 3. Pengaturan
        $settings = [
            ['key' => 'DISKON_LANGGANAN', 'value' => '10'],
            ['key' => 'DISKON_TEMAN', 'value' => '20'],
            ['key' => 'PPN', 'value' => '11'],
            ['key' => 'FORMAT_INVOICE', 'value' => 'INV-[YYYYMMDD]-[XXX]'],
            ['key' => 'TEMA_WARNA', 'value' => 'BLUE']
        ];
        foreach ($settings as $setting) { Setting::create($setting); }

        // 4. Supplier
        $suppliers = [
            ['kode_supplier' => 'SUP-001', 'nama_supplier' => 'PT Astra Otoparts', 'pic' => json_encode([['nama' => 'Bapak Budi']]), 'nomor_hp' => '0811111111', 'email' => 'budi@astra.co.id', 'status_supplier' => 'Aktif'],
            ['kode_supplier' => 'SUP-002', 'nama_supplier' => 'CV Maju Motor', 'pic' => json_encode([['nama' => 'Bapak Andi']]), 'nomor_hp' => '0822222222', 'email' => 'andi@majumotor.com', 'status_supplier' => 'Aktif'],
            ['kode_supplier' => 'SUP-003', 'nama_supplier' => 'Toko Sparepart Jaya', 'pic' => json_encode([['nama' => 'Bapak Candra']]), 'nomor_hp' => '0833333333', 'email' => 'candra@jayasparepart.com', 'status_supplier' => 'Aktif'],
            ['kode_supplier' => 'SUP-004', 'nama_supplier' => 'Distributor Oli Pertamina', 'pic' => json_encode([['nama' => 'Bapak Dedi']]), 'nomor_hp' => '0844444444', 'email' => 'dedi@pertamina.com', 'status_supplier' => 'Aktif'],
            ['kode_supplier' => 'SUP-005', 'nama_supplier' => 'Grosir Ban IRC', 'pic' => json_encode([['nama' => 'Bapak Eko']]), 'nomor_hp' => '0855555555', 'email' => 'eko@grosirban.com', 'status_supplier' => 'Aktif'],
            ['kode_supplier' => 'SUP-006', 'nama_supplier' => 'Agen Helm KYT (Tutup)', 'pic' => json_encode([['nama' => 'Bapak Feri']]), 'nomor_hp' => '0866666666', 'email' => 'feri@agenhelm.com', 'status_supplier' => 'Non Aktif']
        ];
        foreach ($suppliers as $sup) { Supplier::create($sup); }

        // 5. Barang (Kode format BRG-0001)
        $barangs = [
            ['kode_barang' => 'BRG-0001', 'barcode1' => '8991234567890', 'barcode2' => '', 'nama_barang' => 'Oli Mesin Pertamina Enduro 4T 1L', 'lokasi_rak' => 'Rak A1', 'kategori' => 'Oli', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0002', 'barcode1' => '8999876543210', 'barcode2' => '', 'nama_barang' => 'Busi NGK C7HSA', 'lokasi_rak' => 'Rak B2', 'kategori' => 'Busi', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0003', 'barcode1' => '8991122334455', 'barcode2' => '', 'nama_barang' => 'Kampas Rem Depan Supra X 125', 'lokasi_rak' => 'Rak C1', 'kategori' => 'Kampas Rem', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0004', 'barcode1' => '8995544332211', 'barcode2' => '', 'nama_barang' => 'Ban Luar IRC 70/90-17', 'lokasi_rak' => 'Gudang Belakang', 'kategori' => 'Ban', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0005', 'barcode1' => '8996677889900', 'barcode2' => '', 'nama_barang' => 'Helm KYT DJ Maru Solid Hitam', 'lokasi_rak' => 'Rak Display', 'kategori' => 'Helm', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0006', 'barcode1' => '8990099887766', 'barcode2' => '', 'nama_barang' => 'Aki GS Astra GTZ5S', 'lokasi_rak' => 'Rak D3', 'kategori' => 'Aki', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0007', 'barcode1' => '8992233445566', 'barcode2' => '', 'nama_barang' => 'Rantai Keteng GL Pro', 'lokasi_rak' => 'Rak E1', 'kategori' => 'Rantai', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0008', 'barcode1' => '8997788990011', 'barcode2' => '', 'nama_barang' => 'Lampu Depan Osram H6', 'lokasi_rak' => 'Rak F2', 'kategori' => 'Lampu', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0009', 'barcode1' => '8991100229933', 'barcode2' => '', 'nama_barang' => 'Filter Udara Vario 125', 'lokasi_rak' => 'Rak G4', 'kategori' => 'Filter', 'status_barang' => 'Aktif'],
            ['kode_barang' => 'BRG-0010', 'barcode1' => '8992233112233', 'barcode2' => '', 'nama_barang' => 'Spion Standar Yamaha (Tidak Dijual)', 'lokasi_rak' => 'Gudang Atas', 'kategori' => 'Spion', 'status_barang' => 'Non Aktif'],
        ];
        foreach ($barangs as $brg) { Barang::create($brg); }

        // 6. BarangSupplier & Harga
        $hargaData = [
            1 => [4, 35000, 45000, 42000, 40000, 100],
            2 => [1, 10000, 15000, 14000, 13000, 50],
            3 => [1, 25000, 35000, 33000, 30000, 80],
            4 => [5, 120000, 150000, 145000, 140000, 20],
            5 => [6, 250000, 300000, 290000, 280000, 10],
            6 => [1, 180000, 220000, 210000, 200000, 15],
            7 => [1, 45000, 60000, 55000, 50000, 25],
            8 => [2, 15000, 25000, 23000, 20000, 40],
            9 => [1, 30000, 45000, 42000, 40000, 30],
            10=> [2, 20000, 30000, 28000, 26000, 0]
        ];

        foreach ($hargaData as $bId => $d) {
            BarangSupplier::create([
                'barang_id' => $bId, 'supplier_id' => $d[0], 'harga_beli' => $d[1], 'diskon_persen' => 0, 
                'satuan' => 'Pcs', 'isi_per_box' => 10, 'stok_saat_ini' => $d[5], 'minimum_stok' => 5, 
                'lokasi_rak' => 'Rak Utama', 'kode_barang_supplier' => 'SUP-BRG-'.$bId, 'is_utama' => true, 
                'status' => 'Aktif', 'tanggal_masuk' => Carbon::now()->subMonths(3)
            ]);

            Harga::create([
                'barang_id' => $bId, 'harga_regular' => $d[2], 'harga_langganan' => $d[3], 'harga_teman' => $d[4], 
                'tanggal_berlaku' => Carbon::now()->subMonths(3), 'status_harga' => 'Aktif', 'keterangan_perubahan' => 'Harga Awal'
            ]);
            
            if ($d[5] > 0) {
                StockMovement::create([
                    'barang_id' => $bId, 'supplier_id' => $d[0], 'user_id' => 1, 'tipe_pergerakan' => 'IN_PURCHASE', 
                    'qty_box' => 1, 'qty_pcs' => $d[5], 'harga_beli' => $d[1], 'alasan_perubahan' => 'Stok Awal',
                    'created_at' => Carbon::now()->subMonths(3)
                ]);
            }
        }

        // 7. Penjualan (10 transaksi dengan format INV-YYYYMMDD-XXX)
        $now = Carbon::now();
        $dateStr = $now->format('Ymd');
        $invoices = [];
        for ($i=1; $i<=10; $i++) {
            $invNo = "INV-{$dateStr}-" . str_pad($i, 4, '0', STR_PAD_LEFT);
            $invoices[] = [
                'no_invoice' => $invNo,
                'user_id' => 2, // Kasir 1
                'kategori_customer' => $i % 3 === 0 ? 'Langganan' : 'Umum',
                'subtotal' => 0,
                'potongan' => 0,
                'total' => 0,
                'metode_pembayaran' => $i % 4 === 0 ? 'Transfer' : 'Cash',
                'detail_pembayaran' => $i % 4 === 0 ? 'BCA' : '',
                'kembalian' => 0,
                'status_transaksi' => 'Selesai',
                'created_at' => $now->copy()->subDays(10 - $i)->setHour(10 + ($i%8))
            ];
        }

        foreach ($invoices as $idx => $inv) {
            // Pilih 1-3 barang random untuk penjualan ini
            $bId = ($idx % 9) + 1;
            $qty = ($idx % 2) + 1;
            $hargaSatuan = $hargaData[$bId][2]; // harga regular
            
            if ($inv['kategori_customer'] === 'Langganan') $hargaSatuan = $hargaData[$bId][3];
            
            $subtotal = $hargaSatuan * $qty;
            
            $inv['subtotal'] = $subtotal;
            $inv['total'] = $subtotal;
            
            // Set kembalian if Cash
            if ($inv['metode_pembayaran'] === 'Cash') {
                $bayar = ceil($subtotal / 50000) * 50000;
                $inv['kembalian'] = $bayar - $subtotal;
            }
            
            $p = Penjualan::create($inv);
            
            PenjualanDetail::create([
                'penjualan_id' => $p->id,
                'barang_id' => $bId,
                'qty' => $qty,
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal
            ]);
            
            // Kurangi stok di pivot (dummy saja)
            $bs = BarangSupplier::where('barang_id', $bId)->where('is_utama', true)->first();
            if ($bs) {
                $bs->stok_saat_ini -= $qty;
                $bs->save();
                
                StockMovement::create([
                    'barang_id' => $bId, 'supplier_id' => $bs->supplier_id, 'user_id' => 2, 'tipe_pergerakan' => 'OUT_SALE', 
                    'qty_box' => 0, 'qty_pcs' => $qty, 'alasan_perubahan' => "Penjualan ($p->no_invoice)",
                    'created_at' => $p->created_at
                ]);
            }
        }

        // 8. Return Transactions (5 transactions)
        $returns = [];
        $returnDateStr = $now->format('Ymd');
        for ($i=1; $i<=5; $i++) {
            $retNo = "RET-{$returnDateStr}-" . str_pad($i, 4, '0', STR_PAD_LEFT);
            $p = Penjualan::find($i); // Ambil dari transaksi penjualan 1-5
            $pd = PenjualanDetail::where('penjualan_id', $p->id)->first();
            
            if (!$pd) continue;
            
            $jenisReturn = $i % 2 === 0 ? 'Refund Tunai' : 'Tukar Barang Sama';
            $selisih = $jenisReturn === 'Refund Tunai' ? -($pd->harga_satuan * 1) : 0;
            
            $rt = ReturnTransaction::create([
                'no_return' => $retNo,
                'no_invoice' => $p->no_invoice,
                'user_id' => 2,
                'jenis_return' => $jenisReturn,
                'selisih_harga' => $selisih,
                'alasan_return' => 'Barang cacat pabrik',
                'status' => 'Selesai',
                'created_at' => $now->copy()->subDays(5 - $i)->setHour(15)
            ]);
            
            ReturnDetail::create([
                'return_transaction_id' => $rt->id,
                'barang_direturn_id' => $pd->barang_id,
                'qty_direturn' => 1,
                'barang_pengganti_id' => $jenisReturn === 'Tukar Barang Sama' ? $pd->barang_id : null,
                'qty_pengganti' => $jenisReturn === 'Tukar Barang Sama' ? 1 : 0
            ]);
            
            // Masuk ke karantina
            BarangReturn::create([
                'no_invoice_asal' => $p->no_invoice,
                'barang_id' => $pd->barang_id,
                'qty_rusak' => 1,
                'alasan' => 'Retur dari Pelanggan',
                'user_id' => 2,
                'created_at' => $rt->created_at
            ]);
            
            // Return ke Supplier (untuk transaksi genap)
            if ($i % 2 === 0) {
                $bs = BarangSupplier::where('barang_id', $pd->barang_id)->first();
                if ($bs) {
                    ReturnSupplier::create([
                        'barang_id' => $pd->barang_id,
                        'supplier_id' => $bs->supplier_id,
                        'qty_retur' => 1,
                        'harga_beli' => $bs->harga_beli,
                        'no_invoice_supplier' => 'INV-SUP-00' . $i,
                        'user_id' => 2,
                        'created_at' => $now->copy()->subDays(4 - $i)->setHour(10)
                    ]);
                    
                    // Hapus barang dari karantina karena sudah diretur
                    $karantina = BarangReturn::where('no_invoice_asal', $p->no_invoice)->where('barang_id', $pd->barang_id)->first();
                    if ($karantina) $karantina->delete();
                }
            }
        }
    }
}
