<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Supplier;
use App\Models\SystemLog;
use App\Models\LogActivity;
use App\Models\User;

class DummyExtraSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

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

        echo "Generating 100 SystemLogs...\n";
        DB::beginTransaction();
        try {
            for ($i = 0; $i < 100; $i++) {
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
            echo "100 SystemLogs created successfully!\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error generating SystemLogs: " . $e->getMessage() . "\n";
        }

        $user = User::first();
        if ($user) {
            echo "Generating 200 Extra LogActivities...\n";
            DB::beginTransaction();
            try {
                for ($i = 0; $i < 200; $i++) {
                    LogActivity::create([
                        'user_id' => $user->id,
                        'action' => $faker->randomElement(['Login', 'Logout', 'Update Profile', 'Delete Barang', 'Print Invoice', 'Export Excel']),
                        'module' => $faker->randomElement(['Dashboard', 'Laporan', 'Pengaturan', 'User Management', 'Penjualan']),
                        'details' => $faker->sentence(),
                        'created_at' => $faker->dateTimeBetween('-2 months', 'now')
                    ]);
                }
                DB::commit();
                echo "200 Extra LogActivities created successfully!\n";
            } catch (\Exception $e) {
                DB::rollBack();
                echo "Error generating extra LogActivity: " . $e->getMessage() . "\n";
            }
        }
    }
}
