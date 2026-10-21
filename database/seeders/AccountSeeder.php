<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk 5 Akun Baku (Chart of Accounts).
 * Sumber: docs/draft_rincian_project_laravel.md §5 & docs/schema.dbml
 */
class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'code' => '1000',
                'name' => 'Kas / Payment Gateway',
                'account_type' => 'aset',
            ],
            [
                'code' => '2000',
                'name' => 'Saldo Toko (available)',
                'account_type' => 'liabilitas',
            ],
            [
                'code' => '2100',
                'name' => 'Saldo Hold Refund',
                'account_type' => 'liabilitas',
            ],
            [
                'code' => '2110',
                'name' => 'Saldo Hold Withdrawal',
                'account_type' => 'liabilitas',
            ],
            [
                'code' => '3000',
                'name' => 'Pendapatan Komisi Platform',
                'account_type' => 'pendapatan',
            ],
        ];

        foreach ($accounts as $row) {
            Account::query()->updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name'], 'account_type' => $row['account_type']]
            );
        }
    }
}
