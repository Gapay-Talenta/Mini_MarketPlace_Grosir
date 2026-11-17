<?php

use App\Models\CommissionRate;
use App\Models\Store;
use App\Models\User;
use App\Modules\Admin\Services\ChangeCommissionSchedule;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
        'email_verified_at' => now(),
    ]);
    $this->actingAs($admin);

    $this->store = Store::query()->create([
        'name' => 'Toko Batas Komisi',
        'slug' => 'toko-batas-komisi',
        'status' => 'aktif',
    ]);

    CommissionRate::query()->create([
        'store_id' => $this->store->id,
        'rate_percent' => '15.00',
        'valid_from' => CarbonImmutable::parse('2026-09-01 00:00:00'),
        'valid_to' => null,
    ]);

    $this->switchAt = CarbonImmutable::parse('2026-10-01 00:00:00');
    app(ChangeCommissionSchedule::class)->handle($this->store, '20.00', $this->switchAt);
});

test('versi komisi lama berakhir tepat saat versi baru dimulai tanpa celah', function () {
    [$old, $new] = CommissionRate::query()
        ->withoutGlobalScope('tenant')
        ->where('store_id', $this->store->id)
        ->orderBy('valid_from')
        ->get()
        ->all();

    expect($old->valid_to?->eq($this->switchAt))->toBeTrue('valid_to versi lama harus sama persis dengan waktu efektif')
        ->and($new->valid_from->eq($old->valid_to))->toBeTrue('versi baru harus bersambung tanpa celah')
        ->and($new->valid_to)->toBeNull();
});

test('setiap instan waktu di sekitar pergantian hanya mengembalikan tepat satu skema komisi', function () {
    $cases = [
        ['instant' => $this->switchAt->subDay(), 'rate' => '15.00'],
        ['instant' => $this->switchAt->subSecond(), 'rate' => '15.00'],
        ['instant' => $this->switchAt->subMicrosecond(), 'rate' => '15.00'],
        ['instant' => $this->switchAt, 'rate' => '20.00'],
        ['instant' => $this->switchAt->addSecond(), 'rate' => '20.00'],
    ];

    foreach ($cases as $case) {
        $schemes = CommissionRate::query()
            ->withoutGlobalScope('tenant')
            ->where('store_id', $this->store->id)
            ->effectiveAt($case['instant'])
            ->get();

        expect($schemes)->toHaveCount(1)
            ->and($schemes->first()?->rate_percent)->toBe($case['rate']);
    }
});

test('tidak ada skema komisi yang berlaku sebelum versi pertama diterbitkan', function () {
    $schemes = CommissionRate::query()
        ->withoutGlobalScope('tenant')
        ->where('store_id', $this->store->id)
        ->effectiveAt(CarbonImmutable::parse('2026-08-31 23:59:59'))
        ->get();

    expect($schemes)->toHaveCount(0);
});
