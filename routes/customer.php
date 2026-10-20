<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function (string $store) {
    return view('customer.home', ['store' => $store]);
})->name('home');
