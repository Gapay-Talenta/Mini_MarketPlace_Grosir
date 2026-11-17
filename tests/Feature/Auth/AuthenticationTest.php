<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('login screen renders mockup layout and branding', function () {
    $response = $this->get(route('login'));

    $response->assertOk()
        ->assertSee('HUB TOKO GROSIR')
        ->assertSee('Masuk ke Akun')
        ->assertSee('Daftar sebagai pembeli')
        ->assertSee('Lupa kata sandi?');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Anda berhasil keluar dari akun.');

    $this->assertGuest();
});

test('portal header renders account menu and logout confirmation modal for authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'Budi Santoso',
        'role' => null,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->get(route('customer.home', ['store' => 'toko-berkah']))
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertSee('Keluar dari akun?')
        ->assertSee('Ya, Keluar');
});
