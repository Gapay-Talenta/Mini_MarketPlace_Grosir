<?php

use App\Models\Store;
use App\Models\StoreAdmin;
use App\Models\User;
use App\Support\Auth\LoginLockout;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear(md5('login127.0.0.1'));
});

test('sandi salah dan surel tidak dikenal mendapat pesan kesalahan generik yang sama', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah-sandi'])
        ->assertSessionHasErrors(['email' => 'Surel atau kata sandi tidak sesuai.']);

    $this->post(route('login.store'), ['email' => 'tidak-ada@grosir.test', 'password' => 'apa-saja'])
        ->assertSessionHasErrors(['email' => 'Surel atau kata sandi tidak sesuai.']);

    $this->assertGuest();
});

test('lima kali gagal dalam sepuluh menit mengunci akun meski kata sandi benar', function () {
    $user = User::factory()->create();

    foreach (range(1, LoginLockout::MAX_FAILURES) as $ignored) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah-sandi']);
    }

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toContain('Akun terkunci sementara');
    $this->assertGuest();
});

test('kunci akun berakhir setelah lima belas menit', function () {
    $user = User::factory()->create();

    foreach (range(1, LoginLockout::MAX_FAILURES) as $ignored) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah-sandi']);
    }

    $this->travel(14)->minutes();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->travel(2)->minutes();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
});

test('kegagalan yang tersebar melewati jendela sepuluh menit tidak mengunci akun', function () {
    $user = User::factory()->create();

    foreach (range(1, LoginLockout::MAX_FAILURES - 1) as $ignored) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah-sandi']);
    }

    $this->travel(11)->minutes();
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah-sandi']);
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
});

test('login berhasil mereset penghitung kegagalan', function () {
    $user = User::factory()->create();

    foreach (range(1, LoginLockout::MAX_FAILURES - 1) as $ignored) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah-sandi']);
    }

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasNoErrors();

    $this->post(route('logout'));

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah-sandi']);
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
});

test('akun nonaktif ditolak dengan pesan generik', function () {
    $user = User::factory()->create(['status' => 'suspended']);

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors(['email' => 'Surel atau kata sandi tidak sesuai.']);

    $this->assertGuest();
});

test('admin hub dan operator toko diarahkan ke dasbor sesuai perannya', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));
    $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));

    $this->post(route('logout'));

    $store = Store::query()->create(['name' => 'Toko Operator', 'slug' => 'toko-operator', 'status' => 'aktif']);
    $operator = User::factory()->create(['role' => 'tenant', 'status' => 'active']);
    StoreAdmin::query()->create(['user_id' => $operator->id, 'store_id' => $store->id, 'role' => 'operator']);

    $this->post(route('login.store'), ['email' => $operator->email, 'password' => 'password']);
    $this->get(route('dashboard'))->assertRedirect(route('tenant.dashboard', ['tenant' => $store->slug]));
    $this->get(route('tenant.dashboard', ['tenant' => $store->slug]))->assertOk();
});

test('halaman login berbahasa indonesia dan menampilkan kebijakan penguncian', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk')
        ->assertSee('Kata sandi')
        ->assertSee('5× gagal dalam 10 menit → akun terkunci 15 menit.');
});

test('masa aktif sesi dikonfigurasi delapan jam tidak aktif', function () {
    expect(config('session.lifetime'))->toBe(480);
});
