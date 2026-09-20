<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DummyUserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        echo "Generating 100 Dummy Users...\n";
        DB::beginTransaction();
        try {
            // Pastikan roles ada
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
    }
}
