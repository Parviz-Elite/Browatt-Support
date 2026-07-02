<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return Inertia::render('Auth/Login', [
        'contact' => [
            'site' => 'https://browatt.com/',
            'email' => 'info@browatt.com',
            'instagram' => 'browatt.co',
        ],
    ]);
})->name('login');
