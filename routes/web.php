<?php

use App\Actions\Auth\RequestLoginOtp;
use App\Actions\Auth\VerifyLoginOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Auth/Login', [
        'contact' => [
            'site' => 'https://browatt.com/',
            'email' => 'info@browatt.com',
            'instagram' => 'browatt.co',
        ],
        'otp' => [
            'resendSeconds' => (int) config('otp.resend_seconds', 60),
            'codeLength' => (int) config('otp.code_length', 6),
        ],
    ]);
})->name('login');

Route::middleware('guest')->group(function () {
    Route::post('/login/otp', RequestLoginOtp::class)
        ->middleware('throttle:otp-requests')
        ->name('login.otp.request');

    Route::post('/login/otp/verify', VerifyLoginOtp::class)
        ->middleware('throttle:otp-verifications')
        ->name('login.otp.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $activeWarrantiesCount = 0;

        if (Schema::hasTable('warranties')) {
            $query = DB::table('warranties')->whereNull('deleted_at');

            if (! auth()->user()->hasRole('general_manager')) {
                $query->where('user_id', auth()->id());
            }

            $activeWarrantiesCount = $query->count();
        }

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'activeWarrantiesCount' => $activeWarrantiesCount,
            ],
        ]);
    })->name('dashboard');

    Route::prefix('warranties')->name('warranties.')->group(function () {
        Route::get('/activate', function () {
            return Inertia::render('Dashboard/Warranty/Activate');
        })->name('activate');

        Route::get('/mine', function () {
            return Inertia::render('Dashboard/Warranty/MyWarranties');
        })->name('mine');
    });

    Route::middleware('role:general_manager')->group(function () {
        Route::get('/warranties', function () {
            return Inertia::render('Dashboard/Warranty/List');
        })->name('admin.warranties.index');

        Route::get('/users', function () {
            return Inertia::render('Dashboard/Users/Index');
        })->name('admin.users.index');

        Route::get('/roles', function () {
            return Inertia::render('Dashboard/Roles/Index');
        })->name('admin.roles.index');
    });
});
