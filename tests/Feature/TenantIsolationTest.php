<?php

namespace Tests\Feature;

use App\Jobs\CountStoreProducts;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreAdmin;
use App\Models\User;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use App\Policies\ProductPolicy;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Matriks Pengujian Isolasi Multi-Tenant (Allow/Deny Toko A vs Toko B).
 * Memenuhi Acceptance Test Wajib #1 (§12 Draft).
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: Membuat toko beserta sejumlah produk.
     *
     * @return array{0: Store, 1: Collection<int, Product>}
     */
    private function storeWithProducts(int $count, string $status = 'aktif', string $productStatus = 'aktif'): array
    {
        $store = Store::query()->create([
            'slug' => 'toko-'.fake()->unique()->slug(),
            'name' => fake()->company(),
            'status' => $status,
        ]);

        $products = new Collection;

        for ($i = 0; $i < $count; $i++) {
            $products->push(Product::query()->create([
                'store_id' => $store->id,
                'name' => fake()->words(3, true),
                'category' => 'bahan_pangan',
                'sales_unit' => 'pcs',
                'weight_gram' => 500,
                'stock' => 100,
                'status' => $productStatus,
            ]));
        }

        return [$store, $products];
    }

    /**
     * Helper: Mendaftarkan user sebagai admin sah dari toko tertentu.
     */
    private function memberOf(Store $store): User
    {
        $user = User::factory()->create([
            'role' => 'tenant',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        StoreAdmin::query()->create([
            'user_id' => $user->id,
            'store_id' => $store->id,
            'role' => 'operator',
        ]);

        return $user;
    }

    public function test_global_scope_returns_only_active_store_rows(): void
    {
        [$storeA] = $this->storeWithProducts(2);
        $this->storeWithProducts(3); // Toko B

        app(TenantContext::class)->set($storeA);

        $this->assertSame(2, Product::query()->count());
        $this->assertTrue(Product::query()->get()->every(fn (Product $p) => $p->store_id === $storeA->id));
    }

    public function test_without_context_scope_is_not_applied(): void
    {
        $this->storeWithProducts(2);
        $this->storeWithProducts(3);

        // Di luar konteks internal toko (tanpa context), query tidak dibatasi scope
        $this->assertSame(5, Product::query()->count());
    }

    public function test_context_is_reset_between_requests(): void
    {
        [$storeA] = $this->storeWithProducts(1);
        app(TenantContext::class)->set($storeA);
        $this->assertTrue(app(TenantContext::class)->has());

        // Simulasi siklus reset antar request HTTP atau job worker
        $this->app->forgetScopedInstances();

        $this->assertFalse(app(TenantContext::class)->has(), 'TenantContext bocor ke lifecycle berikutnya!');
    }

    public function test_store_id_is_autofilled_on_create_within_context(): void
    {
        [$storeA] = $this->storeWithProducts(0);
        app(TenantContext::class)->set($storeA);

        // Simpan produk tanpa menyertakan kolom store_id secara manual
        $product = Product::query()->create([
            'name' => 'Gula Pasir 1kg',
            'category' => 'bahan_pangan',
            'sales_unit' => 'pcs',
            'weight_gram' => 1000,
            'stock' => 50,
            'status' => 'aktif',
        ]);

        $this->assertSame($storeA->id, $product->store_id);
    }

    public function test_product_index_returns_only_own_store(): void
    {
        [$storeA] = $this->storeWithProducts(2);
        $this->storeWithProducts(3); // Toko B

        $this->actingAs($this->memberOf($storeA));

        $response = $this->getJson(route('tenant.products.index', ['tenant' => $storeA->slug]));

        $response->assertOk();
        $this->assertCount(2, $response->json('products'));
    }

    public function test_scoped_binding_blocks_cross_store_product(): void
    {
        [$storeA] = $this->storeWithProducts(1);
        [, $productsB] = $this->storeWithProducts(1);
        $productB = $productsB->first();

        $this->actingAs($this->memberOf($storeA));

        // URL Toko A mencoba mengakses ID produk milik Toko B -> Wajib 404 Not Found via scopeBindings
        $this->getJson(route('tenant.products.show', [
            'tenant' => $storeA->slug,
            'product' => $productB->id,
        ]))->assertNotFound();
    }

    public function test_member_can_update_own_product(): void
    {
        [$storeA, $productsA] = $this->storeWithProducts(1);
        $productA = $productsA->first();

        $this->actingAs($this->memberOf($storeA));

        $response = $this->patchJson(route('tenant.products.update', [
            'tenant' => $storeA->slug,
            'product' => $productA->id,
        ]), [
            'status' => 'arsip',
        ]);

        $response->assertOk()->assertJson(['ok' => true, 'status' => 'arsip']);
        $this->assertSame('arsip', $productA->refresh()->status);
    }

    public function test_policy_denies_cross_store_update(): void
    {
        [$storeA] = $this->storeWithProducts(0);
        [, $productsB] = $this->storeWithProducts(1);
        $productB = $productsB->first();

        app(TenantContext::class)->set($storeA); // Toko aktif = Toko A

        // Policy wajib menolak bila produk bukan milik toko aktif di context
        $this->assertFalse(
            (new ProductPolicy)->update(new User, $productB),
            'Policy harus menolak produk milik toko lain'
        );
    }

    public function test_public_catalog_query_no_inactive_store_or_archived_product_leak(): void
    {
        // 1. Toko Aktif + Produk Aktif (Harus Tampil)
        $this->storeWithProducts(2, status: 'aktif', productStatus: 'aktif');

        // 2. Toko Aktif + Produk Arsip (Tidak Boleh Tampil)
        $this->storeWithProducts(1, status: 'aktif', productStatus: 'arsip');

        // 3. Toko Nonaktif/Suspend + Produk Aktif (Tidak Boleh Tampil)
        $this->storeWithProducts(3, status: 'nonaktif', productStatus: 'aktif');

        $products = app(PublicCatalogQuery::class)->forMarketplace();

        $this->assertCount(2, $products, 'Katalog hanya boleh menampilkan produk aktif dari toko yang berstatus aktif');
    }

    public function test_job_sets_and_clears_context_per_store(): void
    {
        [$storeA] = $this->storeWithProducts(2);
        [$storeB] = $this->storeWithProducts(4);

        $context = app(TenantContext::class);

        // Job Toko A
        $jobA = new CountStoreProducts($storeA->id);
        $jobA->handle($context);
        $this->assertSame(2, $jobA->result);
        $this->assertFalse($context->has(), 'Context harus steril setelah Job A selesai');

        // Job Toko B (pada worker yang sama)
        $jobB = new CountStoreProducts($storeB->id);
        $jobB->handle($context);
        $this->assertSame(4, $jobB->result, 'Job B tidak boleh terpengaruh oleh state Toko A');
        $this->assertFalse($context->has(), 'Context harus steril setelah Job B selesai');
    }
}
