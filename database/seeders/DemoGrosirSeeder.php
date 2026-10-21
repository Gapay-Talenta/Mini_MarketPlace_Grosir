<?php

namespace Database\Seeders;

use App\Models\BalanceProjection;
use App\Models\CommissionRate;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Store;
use App\Models\StoreAdmin;
use App\Models\StoreBankAccount;
use App\Models\StoreShippingRate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder data demo deterministik & idempoten untuk 2 Toko Grosir,
 * produk dengan tier harga, rekening bank, komisi, tarif ongkir, dan balance projections.
 */
class DemoGrosirSeeder extends Seeder
{
    public function run(): void
    {
        // 1. User Operator Toko 2 (jika belum ada)
        $operator2 = User::query()->firstOrNew(['email' => 'plastik@grosir.test']);
        $operator2->forceFill([
            'name' => 'Admin Sumber Plastik',
            'phone' => '081234567892',
            'role' => 'tenant',
            'status' => 'active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->save();

        // Cari user operator 1 (tenant@grosir.test dari DatabaseSeeder)
        $operator1 = User::query()->firstOrNew(['email' => 'tenant@grosir.test']);
        $operator1->forceFill([
            'name' => 'Operator Toko Sembako',
            'phone' => '081234567891',
            'role' => 'tenant',
            'status' => 'active',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->save();

        // 2. Definisi Blueprint Toko & Produk
        $storesBlueprint = [
            [
                'name' => 'Sembako Berkah Grosir',
                'slug' => 'sembako-berkah',
                'description' => 'Pusat kulakan sembako beras, minyak, dan gula skala grosir.',
                'operator_id' => $operator1->id,
                'rate_per_kg' => 8000,
                'commission_percent' => '10.00',
                'bank' => [
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'last4' => '7890',
                    'holder' => 'PT Sembako Berkah Nusantara',
                ],
                'products' => [
                    [
                        'name' => 'Beras Ramos Super 50kg',
                        'category' => 'bahan_pangan',
                        'sales_unit' => 'karung',
                        'weight_gram' => 50000,
                        'stock' => 200,
                        'normal_price' => 650000,
                        'tiers' => [
                            ['min_qty' => 5, 'amount' => 630000],
                            ['min_qty' => 20, 'amount' => 610000],
                        ],
                    ],
                    [
                        'name' => 'Minyak Goreng Sawit 1 Dus (12x1L)',
                        'category' => 'bahan_pangan',
                        'sales_unit' => 'kardus',
                        'weight_gram' => 12000,
                        'stock' => 150,
                        'normal_price' => 180000,
                        'tiers' => [
                            ['min_qty' => 10, 'amount' => 175000],
                        ],
                    ],
                    [
                        'name' => 'Gula Pasir Kristal Putih 50kg',
                        'category' => 'bahan_pangan',
                        'sales_unit' => 'karung',
                        'weight_gram' => 50000,
                        'stock' => 100,
                        'normal_price' => 750000,
                        'tiers' => [
                            ['min_qty' => 5, 'amount' => 730000],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Sumber Plastik & Packing Hub',
                'slug' => 'sumber-plastik',
                'description' => 'Grosir aneka kantong plastik, bubble wrap, dan kardus packing.',
                'operator_id' => $operator2->id,
                'rate_per_kg' => 10000,
                'commission_percent' => '12.00',
                'bank' => [
                    'bank_name' => 'Mandiri',
                    'account_number' => '9876543210123',
                    'last4' => '0123',
                    'holder' => 'CV Sumber Plastik Abadi',
                ],
                'products' => [
                    [
                        'name' => 'Kantong Plastik HD Tebal 1 Pack (100 pcs)',
                        'category' => 'kebersihan',
                        'sales_unit' => 'pack',
                        'weight_gram' => 1000,
                        'stock' => 500,
                        'normal_price' => 25000,
                        'tiers' => [
                            ['min_qty' => 20, 'amount' => 22000],
                            ['min_qty' => 100, 'amount' => 20000],
                        ],
                    ],
                    [
                        'name' => 'Kardus Polos Single Wall A4 (10 pcs)',
                        'category' => 'alat_tulis',
                        'sales_unit' => 'kardus',
                        'weight_gram' => 3000,
                        'stock' => 300,
                        'normal_price' => 45000,
                        'tiers' => [
                            ['min_qty' => 10, 'amount' => 40000],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($storesBlueprint as $spec) {
            // Toko
            $store = Store::query()->firstOrNew(['slug' => $spec['slug']]);
            $store->forceFill([
                'name' => $spec['name'],
                'description' => $spec['description'],
                'status' => 'aktif',
            ])->save();

            // Store Admin Membership
            StoreAdmin::query()->updateOrCreate(
                ['store_id' => $store->id, 'user_id' => $spec['operator_id']],
                ['role' => 'owner']
            );

            // Tarif Ongkir Toko
            StoreShippingRate::query()->updateOrCreate(
                ['store_id' => $store->id],
                ['rate_per_kg' => $spec['rate_per_kg']]
            );

            // Tarif Komisi Toko (effective-dated)
            CommissionRate::query()->updateOrCreate(
                ['store_id' => $store->id, 'valid_from' => now()->startOfYear()],
                ['rate_percent' => $spec['commission_percent'], 'valid_to' => null]
            );

            // Rekening Bank Toko
            StoreBankAccount::query()->updateOrCreate(
                ['store_id' => $store->id, 'account_last4' => $spec['bank']['last4']],
                [
                    'bank_name' => $spec['bank']['bank_name'],
                    'account_number_encrypted' => $spec['bank']['account_number'],
                    'account_holder_name' => $spec['bank']['holder'],
                    'status' => 'verified',
                ]
            );

            // Produk & Tier Harga
            foreach ($spec['products'] as $prodSpec) {
                $product = Product::query()->firstOrNew([
                    'store_id' => $store->id,
                    'name' => $prodSpec['name'],
                ]);
                $product->forceFill([
                    'category' => $prodSpec['category'],
                    'sales_unit' => $prodSpec['sales_unit'],
                    'description' => 'Produk grosir original berkualitas tinggi.',
                    'weight_gram' => $prodSpec['weight_gram'],
                    'stock' => $prodSpec['stock'],
                    'status' => 'aktif',
                ])->save();

                // Harga Normal
                ProductPrice::query()->updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                        'price_type' => 'normal',
                        'min_qty' => null,
                    ],
                    ['amount' => $prodSpec['normal_price']]
                );

                // Tier Harga Grosir
                foreach ($prodSpec['tiers'] as $tier) {
                    ProductPrice::query()->updateOrCreate(
                        [
                            'store_id' => $store->id,
                            'product_id' => $product->id,
                            'price_type' => 'grosir',
                            'min_qty' => $tier['min_qty'],
                        ],
                        ['amount' => $tier['amount']]
                    );
                }
            }

            // Inisialisasi Proyeksi Saldo Toko (Akun 2000, 2100, 2110)
            foreach (['2000', '2100', '2110'] as $code) {
                BalanceProjection::query()->updateOrCreate(
                    ['account_code' => $code, 'store_id' => $store->id],
                    ['balance' => 0, 'updated_at' => now()]
                );
            }
        }

        // Inisialisasi Proyeksi Saldo Platform (1000 Kas & 3000 Komisi Platform)
        foreach (['1000', '3000'] as $code) {
            BalanceProjection::query()->updateOrCreate(
                ['account_code' => $code, 'store_id' => null],
                ['balance' => 0, 'updated_at' => now()]
            );
        }
    }
}
