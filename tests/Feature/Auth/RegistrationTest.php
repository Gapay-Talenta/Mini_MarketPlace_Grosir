<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered with whatsapp field', function () {
    $response = $this->get(route('register'));

    $response->assertOk()
        ->assertSee('Buat Akun Pembeli')
        ->assertSee('Nomor WhatsApp (Wajib)')
        ->assertSee('Target Notifikasi')
        ->assertSee('Daftar & Mulai Belanja');
});

test('new customers can register with whatsapp number and terms acceptance', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Budi Santoso (Toko Berkah)',
        'email' => 'budi.berkah@grosir.test',
        'phone' => '0812-3456-7890',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'budi.berkah@grosir.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->phone)->toBe('081234567890')
        ->and($user->role)->toBeNull()
        ->and($user->status)->toBe('active');
});

test('registration fails when whatsapp number is missing or invalid', function () {
    $this->post(route('register.store'), [
        'name' => 'Budi Santoso',
        'email' => 'budi-missing-phone@grosir.test',
        'phone' => '',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ])->assertSessionHasErrors('phone');

    $this->post(route('register.store'), [
        'name' => 'Budi Santoso',
        'email' => 'budi-invalid-phone@grosir.test',
        'phone' => '12345',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ])->assertSessionHasErrors('phone');

    $this->assertGuest();
});

test('registration fails when whatsapp number is already taken', function () {
    User::factory()->create(['phone' => '081234567890']);

    $this->post(route('register.store'), [
        'name' => 'Pembeli Baru',
        'email' => 'baru@grosir.test',
        'phone' => '081234567890',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => '1',
    ])->assertSessionHasErrors('phone');

    $this->assertGuest();
});

test('registration fails when terms and conditions are not accepted', function () {
    $this->post(route('register.store'), [
        'name' => 'Pembeli Tanpa Setuju',
        'email' => 'tanpa-setuju@grosir.test',
        'phone' => '081298765432',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('terms');

    $this->assertGuest();
});
