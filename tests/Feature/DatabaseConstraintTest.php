<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CommissionRate;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreShippingRate;
use App\Models\SubOrder;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pengujian Penegakan Constraint Basis Data MariaDB (Modul 03).
 * Membuktikan bahwa foreign key komposit anti lintas-toko, idempotency key,
 * serta invariant arsitektur ditegakkan langsung di level engine MariaDB.
 */
class DatabaseConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_price_cannot_reference_another_stores_product(): void
    {
        $storeA = Store::query()->create(['name' => 'Toko A', 'slug' => 'toko-a']);
        $storeB = Store::query()->create(['name' => 'Toko B', 'slug' => 'toko-b']);

        // Produk milik Toko B
        $productB = Product::query()->create([
            'store_id' => $storeB->id,
            'name' => 'Produk Toko B',
            'category' => 'bahan_pangan',
            'sales_unit' => 'pcs',
            'weight_gram' => 500,
            'stock' => 50,
            'status' => 'aktif',
        ]);

        // Ekspektasi: MariaDB menolak harga ber-store_id A menunjuk product milik Toko B (error 1452)
        $this->expectException(QueryException::class);

        DB::table('product_prices')->insert([
            'store_id' => $storeA->id,       // Toko A
            'product_id' => $productB->id,   // Produk milik Toko B -> Pelanggaran Composite FK
            'price_type' => 'normal',
            'amount' => 15000,
            'min_qty' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_order_item_cannot_reference_another_stores_product(): void
    {
        $user = User::query()->create([
            'name' => 'Customer Test',
            'email' => 'user-test@grosir.test',
            'password' => 'secret',
            'phone' => '081234567890',
            'role' => 'customer',
        ]);

        $storeA = Store::query()->create(['name' => 'Toko A', 'slug' => 'toko-a']);
        $storeB = Store::query()->create(['name' => 'Toko B', 'slug' => 'toko-b']);

        $productB = Product::query()->create([
            'store_id' => $storeB->id,
            'name' => 'Produk Toko B',
            'category' => 'minuman',
            'sales_unit' => 'kardus',
            'weight_gram' => 12000,
            'stock' => 10,
            'status' => 'aktif',
        ]);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'subtotal' => 100000,
            'shipping_total' => 10000,
            'grand_total' => 110000,
            'checkout_idempotency_key' => 'idemp-chk-001',
        ]);

        $subOrderA = SubOrder::query()->create([
            'order_id' => $order->id,
            'store_id' => $storeA->id,
            'status' => 'pending_payment',
            'subtotal' => 100000,
            'shipping_fee' => 10000,
            'total_weight_gram' => 12000,
            'rate_per_kg' => 8000,
            'commission_rate_percent' => '10.00',
            'commission_amount' => 10000,
            'total' => 110000,
        ]);

        // Ekspektasi: SubOrder Toko A tidak bisa menunjuk Product Toko B
        $this->expectException(QueryException::class);

        DB::table('order_items')->insert([
            'store_id' => $storeA->id,
            'sub_order_id' => $subOrderA->id,
            'product_id' => $productB->id, // Produk milik Toko B
            'product_name' => $productB->name,
            'price_amount' => 100000,
            'price_type' => 'normal',
            'min_qty' => null,
            'qty' => 1,
            'sales_unit' => 'kardus',
            'weight_gram' => 12000,
            'line_total' => 100000,
            'created_at' => now(),
        ]);
    }

    public function test_duplicate_store_slug_is_rejected(): void
    {
        Store::query()->create(['name' => 'Toko Asli', 'slug' => 'toko-unik']);

        $this->expectException(QueryException::class);

        Store::query()->create(['name' => 'Toko Tiruan', 'slug' => 'toko-unik']);
    }

    public function test_orders_checkout_idempotency_key_is_unique(): void
    {
        $user = User::query()->create([
            'name' => 'Customer Idemp',
            'email' => 'idemp@grosir.test',
            'password' => 'secret',
            'phone' => '081234567899',
            'role' => 'customer',
        ]);

        Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'subtotal' => 50000,
            'shipping_total' => 8000,
            'grand_total' => 58000,
            'checkout_idempotency_key' => 'same-key-123',
        ]);

        $this->expectException(QueryException::class);

        Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'subtotal' => 50000,
            'shipping_total' => 8000,
            'grand_total' => 58000,
            'checkout_idempotency_key' => 'same-key-123',
        ]);
    }

    public function test_ledger_entry_idempotency_key_is_unique(): void
    {
        $this->seed(AccountSeeder::class);

        $row = fn () => [
            'account_code' => '1000',
            'store_id' => null,
            'debit' => 100000,
            'credit' => 0,
            'idempotency_key' => 'ledger-key-001',
            'reference_type' => 'settlement',
            'reference_id' => 1,
            'created_at' => now(),
        ];

        DB::table('ledger_entries')->insert($row());

        $this->expectException(QueryException::class);

        DB::table('ledger_entries')->insert($row());
    }

    public function test_orders_has_no_store_id_and_supports_multiple_sub_orders(): void
    {
        // 1. Verifikasi orders tidak memiliki store_id
        $this->assertFalse(
            Schema::hasColumn('orders', 'store_id'),
            'Tabel orders TIDAK BOLEH memiliki kolom store_id (platform-scoped checkout).'
        );

        $user = User::query()->create([
            'name' => 'Customer Multi',
            'email' => 'multi@grosir.test',
            'password' => 'secret',
            'phone' => '081234567898',
            'role' => 'customer',
        ]);

        $storeA = Store::query()->create(['name' => 'Toko Sembako A', 'slug' => 'sembako-a']);
        $storeB = Store::query()->create(['name' => 'Toko Plastik B', 'slug' => 'plastik-b']);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'status' => 'pending_payment',
            'subtotal' => 150000,
            'shipping_total' => 20000,
            'grand_total' => 170000,
            'checkout_idempotency_key' => 'multi-order-check-001',
        ]);

        // Buat 2 sub_order untuk 2 toko berbeda dalam 1 checkout
        SubOrder::query()->create([
            'order_id' => $order->id,
            'store_id' => $storeA->id,
            'status' => 'pending_payment',
            'subtotal' => 100000,
            'shipping_fee' => 10000,
            'total_weight_gram' => 5000,
            'rate_per_kg' => 8000,
            'commission_rate_percent' => '10.00',
            'commission_amount' => 10000,
            'total' => 110000,
        ]);

        SubOrder::query()->create([
            'order_id' => $order->id,
            'store_id' => $storeB->id,
            'status' => 'pending_payment',
            'subtotal' => 50000,
            'shipping_fee' => 10000,
            'total_weight_gram' => 2000,
            'rate_per_kg' => 10000,
            'commission_rate_percent' => '12.00',
            'commission_amount' => 6000,
            'total' => 60000,
        ]);

        $this->assertSame(2, $order->subOrders()->count());
    }

    public function test_money_and_rate_attributes_cast_correctly(): void
    {
        $store = Store::query()->create(['name' => 'Toko Cek Cast', 'slug' => 'toko-cek-cast']);

        $shipping = StoreShippingRate::query()->create([
            'store_id' => $store->id,
            'rate_per_kg' => 8500,
        ]);

        $this->assertIsInt($shipping->refresh()->rate_per_kg);
        $this->assertSame(8500, $shipping->rate_per_kg);

        $comm = CommissionRate::query()->create([
            'store_id' => $store->id,
            'rate_percent' => '15.50',
            'valid_from' => now(),
        ]);

        $this->assertSame('15.50', $comm->refresh()->rate_percent);
    }

    public function test_five_chart_of_accounts_exist_and_match_specification(): void
    {
        $this->seed(AccountSeeder::class);

        $codes = Account::query()->pluck('code')->all();

        $this->assertContains('1000', $codes);
        $this->assertContains('2000', $codes);
        $this->assertContains('2100', $codes);
        $this->assertContains('2110', $codes);
        $this->assertContains('3000', $codes);
        $this->assertCount(5, $codes);
    }
}
