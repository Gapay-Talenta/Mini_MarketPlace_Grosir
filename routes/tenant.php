<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function (string $tenant) {
    return view('tenant.dashboard', ['tenant' => $tenant]);
})->name('dashboard');
