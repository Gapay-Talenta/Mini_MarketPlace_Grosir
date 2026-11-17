<?php

use App\Models\AuditLog;
use App\Models\CommissionRate;
use App\Models\Store;
use App\Models\StoreAdmin;
use App\Models\StoreBankAccount;
use App\Models\StoreShippingRate;
use App\Models\User;
use App\Modules\Admin\Services\ManageBankAccount;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

function createHubAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
        'email_verified_at' => now(),
    ]);
}

function createStoreSample(string $slug = 'toko-uji'): Store
{
    return Store::query()->create([
        'name' => 'Toko Grosir Uji',
        'slug' => $slug,
        'description' => 'Toko uji coba',
        'status' => 'aktif',
    ]);
}

test('hanya admin hub yang dapat mengakses halaman manajemen toko', function () {
    $store = createStoreSample();

    // Guest diarahkan ke login
    $this->get(route('admin.stores.index'))->assertRedirect(route('login'));

    // Customer dan tenant ditolak 403
    $customer = User::factory()->create(['role' => 'customer', 'status' => 'active', 'email_verified_at' => now()]);
    $this->actingAs($customer)->get(route('admin.stores.index'))->assertForbidden();

    $tenantUser = User::factory()->create(['role' => 'tenant', 'status' => 'active', 'email_verified_at' => now()]);
    $this->actingAs($tenantUser)->get(route('admin.stores.edit', $store))->assertForbidden();

    // Admin Hub berhasil mengakses
    $this->actingAs(createHubAdmin());
    $this->get(route('admin.stores.index'))->assertOk();
    $this->get(route('admin.stores.create'))->assertOk();
    $this->get(route('admin.stores.edit', $store))->assertOk()->assertSee('Skema Komisi Platform');
});

test('membuat toko baru membentuk komisi aktif, tarif ongkir, dan audit log secara atomik', function () {
    Notification::fake();
    $admin = createHubAdmin();
    $this->actingAs($admin);

    $response = $this->post(route('admin.stores.store'), [
        'name' => 'Sembako Makmur',
        'slug' => 'sembako-makmur',
        'description' => 'Pusat kulakan sembako',
        'commission_rate' => '12.50',
        'rate_per_kg' => '9000',
        'pic_name' => 'Budi Owner',
        'pic_email' => 'budi-makmur@grosir.test',
        'bank_name' => 'BCA',
        'account_holder_name' => 'Budi Owner',
        'account_number' => '1234567890',
    ]);

    $store = Store::query()->where('slug', 'sembako-makmur')->firstOrFail();

    $response->assertRedirect(route('admin.stores.edit', $store));

    expect(CommissionRate::query()->withoutGlobalScope('tenant')->where('store_id', $store->id)->whereNull('valid_to')->count())
        ->toBe(1);
    expect(StoreShippingRate::query()->withoutGlobalScope('tenant')->where('store_id', $store->id)->value('rate_per_kg'))
        ->toBe(9000);
    expect(AuditLog::query()->where('action', 'store_created')->where('subject_id', $store->id)->exists())
        ->toBeTrue();
});

test('slug toko harus unik dan payload kosong ditolak validasi', function () {
    $this->actingAs(createHubAdmin());
    createStoreSample('toko-unik');

    $this->post(route('admin.stores.store'), [])
        ->assertSessionHasErrors([
            'name', 'slug', 'commission_rate', 'rate_per_kg',
            'pic_name', 'pic_email', 'bank_name', 'account_holder_name', 'account_number',
        ]);

    $this->post(route('admin.stores.store'), [
        'name' => 'Toko Duplikat',
        'slug' => 'toko-unik',
        'commission_rate' => '10',
        'rate_per_kg' => '8000',
        'pic_name' => 'Budi',
        'pic_email' => 'budi-dup@grosir.test',
        'bank_name' => 'BRI',
        'account_holder_name' => 'Budi',
        'account_number' => '1234567890',
    ])->assertSessionHasErrors('slug');
});

test('penjadwalan komisi bersifat effective-dated dan menolak tanggal masa lalu', function () {
    $this->actingAs(createHubAdmin());
    $store = createStoreSample();

    CommissionRate::query()->create([
        'store_id' => $store->id,
        'rate_percent' => '10.00',
        'valid_from' => now()->subMonth(),
        'valid_to' => null,
    ]);

    // Tanggal berlaku surut (masa lalu) ditolak
    $this->post(route('admin.stores.commission.store', $store), [
        'commission_rate' => '15',
        'effective_at' => now()->subDay()->format('Y-m-d\TH:i'),
    ])->assertSessionHasErrors('effective_at');

    // Tanggal masa depan berhasil dan menutup versi lama
    $effectiveAt = now()->addDays(2)->startOfMinute();
    $this->post(route('admin.stores.commission.store', $store), [
        'commission_rate' => '15.00',
        'effective_at' => $effectiveAt->format('Y-m-d\TH:i'),
    ])->assertRedirect(route('admin.stores.edit', $store))->assertSessionHasNoErrors();

    $rates = CommissionRate::query()
        ->withoutGlobalScope('tenant')
        ->where('store_id', $store->id)
        ->orderBy('valid_from')
        ->get();

    expect($rates)->toHaveCount(2)
        ->and($rates[0]->valid_to)->not->toBeNull()
        ->and($rates[1]->valid_to)->toBeNull()
        ->and($rates[1]->rate_percent)->toBe('15.00');

    expect(AuditLog::query()->where('action', 'commission_scheduled')->exists())->toBeTrue();
});

test('penjadwalan komisi menolak waktu efektif yang mendahului atau sama dengan versi aktif', function () {
    $this->actingAs(createHubAdmin());
    $store = createStoreSample();

    $futureStart = now()->addDays(5);
    CommissionRate::query()->create([
        'store_id' => $store->id,
        'rate_percent' => '10.00',
        'valid_from' => $futureStart,
        'valid_to' => null,
    ]);

    // Meskipun after:now lolos validasi request, service menolak karena lebih awal dari valid_from aktif
    $this->post(route('admin.stores.commission.store', $store), [
        'commission_rate' => '12.00',
        'effective_at' => now()->addDay()->format('Y-m-d\TH:i'),
    ])->assertSessionHasErrors('effective_at');
});

test('dua skema komisi aktif sekaligus ditolak oleh database guard mariadb', function () {
    $store = createStoreSample();

    $row = fn () => [
        'store_id' => $store->id,
        'rate_percent' => 10.00,
        'valid_from' => now(),
        'valid_to' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('commission_rates')->insert($row());

    expect(fn () => DB::table('commission_rates')->insert($row()))
        ->toThrow(QueryException::class);
});

test('rekening bank disimpan terenkripsi dengan last4 dan audit log tidak membocorkan nomor mentah', function () {
    $this->actingAs(createHubAdmin());
    $store = createStoreSample();

    $this->post(route('admin.stores.bank.store', $store), [
        'bank_name' => 'BCA',
        'account_holder_name' => 'Budi Santoso',
        'account_number' => '1234567890',
    ])->assertRedirect(route('admin.stores.edit', $store));

    $account = StoreBankAccount::query()->where('store_id', $store->id)->firstOrFail();
    $raw = DB::table('store_bank_accounts')->where('id', $account->id)->first();

    expect($raw->account_last4)->toBe('7890')
        ->and($raw->status)->toBe('pending')
        ->and((string) $raw->account_number_encrypted)->not->toContain('1234567890')
        ->and($account->fresh()->account_number_encrypted)->toBe('1234567890');

    $audit = AuditLog::query()->where('action', 'bank_account_added')->firstOrFail();
    expect(json_encode($audit->after))->not->toContain('1234567890');
});

test('admin dapat memverifikasi dan menolak rekening serta mencegah akses lintas toko', function () {
    $this->actingAs(createHubAdmin());
    $storeA = createStoreSample('toko-a');
    $storeB = createStoreSample('toko-b');

    $accountB = app(ManageBankAccount::class)->store($storeB, [
        'bank_name' => 'Mandiri',
        'account_holder_name' => 'Pemilik B',
        'account_number' => '9876543210',
    ]);

    // Coba verifikasi rekening Toko B memakai URL Toko A -> wajib 404
    $this->post(route('admin.stores.bank.verify', [$storeA, $accountB]), ['approve' => 1])
        ->assertNotFound();

    // Verifikasi rekening Toko B pada URL yang sah
    $this->post(route('admin.stores.bank.verify', [$storeB, $accountB]), ['approve' => 1])
        ->assertRedirect(route('admin.stores.edit', $storeB));

    expect($accountB->fresh()->status)->toBe('verified');
    expect(AuditLog::query()->where('action', 'verify_bank_account')->where('subject_id', $accountB->id)->exists())->toBeTrue();

    // Tolak rekening
    $this->post(route('admin.stores.bank.verify', [$storeB, $accountB]), ['approve' => 0])
        ->assertRedirect(route('admin.stores.edit', $storeB));

    expect($accountB->fresh()->status)->toBe('rejected');
});

test('penugasan role berhasil dan sistem melindungi owner terakhir dari pencabutan', function () {
    $this->actingAs(createHubAdmin());
    $store = createStoreSample();
    $owner = User::factory()->create(['role' => 'tenant', 'status' => 'active']);
    $operator = User::factory()->create(['role' => 'tenant', 'status' => 'active']);

    // Tugaskan owner dan operator melalui endpoint
    $this->post(route('admin.stores.roles.store', $store), ['email' => $owner->email, 'role' => 'owner'])
        ->assertSessionHasNoErrors();
    $this->post(route('admin.stores.roles.store', $store), ['email' => $operator->email, 'role' => 'operator'])
        ->assertSessionHasNoErrors();

    expect(StoreAdmin::query()->where('store_id', $store->id)->count())->toBe(2);

    // Mencabut owner terakhir via HTTP harus ditolak dengan error pada 'role'
    $this->delete(route('admin.stores.roles.destroy', [$store, $owner]), ['role' => 'owner'])
        ->assertSessionHasErrors('role');

    expect(StoreAdmin::query()->where('store_id', $store->id)->where('role', 'owner')->exists())->toBeTrue();

    // Mencabut operator diperbolehkan
    $this->delete(route('admin.stores.roles.destroy', [$store, $operator]), ['role' => 'operator'])
        ->assertSessionHasNoErrors();

    expect(StoreAdmin::query()->where('store_id', $store->id)->where('user_id', $operator->id)->exists())->toBeFalse();
});
