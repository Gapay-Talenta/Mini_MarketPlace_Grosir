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
        User::factory()->create([
            'name' => 'Pembeli Grosir',
            'email' => 'customer@grosir.test',
            'role' => null,
            'status' => 'active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // 2. Akun Demo Peran Internal
        $demoUsers = [
            [
                'name' => 'Admin Hub Platform',
                'email' => 'admin@grosir.test',
                'role' => 'admin',
            ],
            [
                'name' => 'Operator Toko Makmur',
                'email' => 'tenant@grosir.test',
                'role' => 'tenant',
            ],
        ];

        foreach ($demoUsers as $row) {
            User::firstOrNew(['email' => $row['email']])
                ->forceFill([
                    'name' => $row['name'],
                    'role' => $row['role'],
                    'status' => 'active',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ])
                ->save();
        }
    }
}
