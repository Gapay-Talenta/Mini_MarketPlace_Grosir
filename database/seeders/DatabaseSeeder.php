<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Akun Pembeli / Customer Biasa
        $customer = User::query()->firstOrNew(['email' => 'customer@grosir.test']);
        $customer->forceFill([
            'name' => 'Pembeli Grosir',
            'phone' => '081234567890',
            'role' => 'customer',
            'status' => 'active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->save();

        // 2. Akun Demo Peran Internal (Admin Hub & Operator Toko)
        $demoUsers = [
            [
                'name' => 'Admin Hub Platform',
                'email' => 'admin@grosir.test',
                'phone' => '081200000001',
                'role' => 'admin',
            ],
            [
                'name' => 'Operator Toko Makmur',
                'email' => 'tenant@grosir.test',
                'phone' => '081200000002',
                'role' => 'tenant',
            ],
        ];

        foreach ($demoUsers as $row) {
            User::query()->firstOrNew(['email' => $row['email']])
                ->forceFill([
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'role' => $row['role'],
                    'status' => 'active',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ])
                ->save();
        }

        // 3. Seeder Akun Finansial (Chart of Accounts)
        $this->call(AccountSeeder::class);

        // 4. Seeder Demo Toko Grosir, Produk, Tier Harga, dan Proyeksi Saldo
        $this->call(DemoGrosirSeeder::class);
    }
}
