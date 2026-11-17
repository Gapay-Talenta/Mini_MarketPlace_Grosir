<?php

use App\Models\AuditLog;
use App\Models\CommissionRate;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreAdmin;
use App\Models\StoreBankAccount;
use App\Models\User;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

function onboardingAdmin(string $name = 'Bagas P.'): User
{
    return User::factory()->create([
        'name' => $name,
        'role' => 'admin',
        'status' => 'active',
        'email_verified_at' => now(),
    ]);
}

function onboardingPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Grosir Bu Rina',
        'slug' => 'grosir-bu-rina',
        'description' => 'Pusat grosir sembako dan kebutuhan warung',
        'commission_rate' => '10',
        'rate_per_kg' => '8000',
        'pic_name' => 'Rina S.',
        'pic_email' => 'rina@grosir.test',
        'bank_name' => 'BCA',
        'account_holder_name' => 'Rina Sari',
        'account_number' => '1234566721',
    ], $overrides);
}

test('onboarding membuat akun owner, rekening bank terenkripsi, dan mengirim tautan atur kata sandi', function () {
    Notification::fake();
    $this->actingAs(onboardingAdmin());

    $this->post(route('admin.stores.store'), onboardingPayload())
        ->assertRedirect()
        ->assertSessionHas('status');

    $store = Store::query()->where('slug', 'grosir-bu-rina')->firstOrFail();
    $owner = User::query()->where('email', 'rina@grosir.test')->firstOrFail();

    expect($owner->role)->toBe('tenant')
        ->and($owner->isActive())->toBeTrue()
        ->and(StoreAdmin::query()->where(['store_id' => $store->id, 'user_id' => $owner->id, 'role' => 'owner'])->exists())->toBeTrue();

    $account = StoreBankAccount::query()->where('store_id', $store->id)->firstOrFail();
    expect($account->account_last4)->toBe('6721')
        ->and($account->status)->toBe('pending')
        ->and((string) $account->getRawOriginal('account_number_encrypted'))->not->toContain('1234566721');

    Notification::assertSentTo($owner, ResetPassword::class);
});

test('onboarding menolak surel penanggung jawab yang sudah ada dan rekening kosong secara atomik', function () {
    $this->actingAs(onboardingAdmin());
    User::factory()->create(['email' => 'rina@grosir.test']);

    $this->post(route('admin.stores.store'), onboardingPayload(['account_number' => '']))
        ->assertSessionHasErrors(['pic_email', 'account_number']);

    expect(Store::query()->where('slug', 'grosir-bu-rina')->exists())->toBeFalse();
});

test('daftar toko menampilkan penanggung jawab, rekening last4, komisi aktif, dan status', function () {
    Notification::fake();
    $this->actingAs(onboardingAdmin());

    $this->post(route('admin.stores.store'), onboardingPayload());

    $this->get(route('admin.stores.index'))
        ->assertOk()
        ->assertSeeInOrder(['Grosir Bu Rina', 'Rina S.', 'BCA ••6721', '10%', 'AKTIF', 'Kelola', 'Nonaktifkan']);
});

test('toko yang dinonaktifkan hilang dari katalog publik namun data historis tetap utuh', function () {
    $this->actingAs(onboardingAdmin());
    $store = Store::query()->create(['name' => 'Toko Aktif', 'slug' => 'toko-aktif', 'status' => 'aktif']);

    $product = Product::query()->create([
        'store_id' => $store->id,
        'name' => 'Beras Grosir 25kg',
        'category' => 'bahan_pangan',
        'sales_unit' => 'karung',
        'weight_gram' => 25000,
        'stock' => 50,
        'status' => 'aktif',
    ]);

    $this->post(route('admin.stores.status', $store), ['status' => 'nonaktif'])
        ->assertRedirect(route('admin.stores.index'));

    expect($store->fresh()->status)->toBe('nonaktif')
        ->and(app(PublicCatalogQuery::class)->forMarketplace()->contains('id', $product->id))->toBeFalse()
        ->and(Product::query()->withoutGlobalScope('tenant')->whereKey($product->id)->exists())->toBeTrue()
        ->and(AuditLog::query()->where(['subject_type' => Store::class, 'subject_id' => $store->id, 'action' => 'store_deactivated'])->exists())->toBeTrue();

    $this->get(route('admin.stores.index'))->assertSee('NONAKTIF')->assertSee('Aktifkan');

    $this->post(route('admin.stores.status', $store), ['status' => 'aktif']);
    expect(app(PublicCatalogQuery::class)->forMarketplace()->contains('id', $product->id))->toBeTrue();
});

test('non admin tidak dapat mengubah status toko dan nilai status di whitelist', function () {
    $store = Store::query()->create(['name' => 'Toko Uji', 'slug' => 'toko-uji', 'status' => 'aktif']);
    $tenant = User::factory()->create(['role' => 'tenant', 'status' => 'active', 'email_verified_at' => now()]);

    $this->actingAs($tenant)
        ->post(route('admin.stores.status', $store), ['status' => 'nonaktif'])
        ->assertForbidden();

    expect($store->fresh()->status)->toBe('aktif');

    $this->actingAs(onboardingAdmin())
        ->post(route('admin.stores.status', $store), ['status' => 'deleted'])
        ->assertSessionHasErrors('status');
});

test('riwayat komisi menampilkan pengelola pengubah dan menolak tanggal berlaku surut', function () {
    Notification::fake();
    $this->actingAs(onboardingAdmin('Bagas P.'));

    $this->post(route('admin.stores.store'), onboardingPayload());
    $store = Store::query()->where('slug', 'grosir-bu-rina')->firstOrFail();

    $this->post(route('admin.stores.commission.store', $store), [
        'commission_rate' => '12',
        'effective_at' => now()->subDay()->format('Y-m-d\TH:i'),
    ])->assertSessionHasErrors('effective_at');

    $this->post(route('admin.stores.commission.store', $store), [
        'commission_rate' => '12',
        'effective_at' => now()->addMonth()->format('Y-m-d\TH:i'),
    ])->assertSessionHasNoErrors();

    expect(CommissionRate::query()->withoutGlobalScope('tenant')->where('store_id', $store->id)->count())->toBe(2);

    $this->get(route('admin.stores.edit', $store))
        ->assertOk()
        ->assertSee('Komisi aktif saat ini')
        ->assertSeeInOrder(['12%', 'oleh Bagas P.', '10%', 'oleh Bagas P.']);
});
