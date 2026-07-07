<?php

use App\Actions\Auth\RequestLoginOtp;
use App\Actions\Auth\VerifyLoginOtp;
use App\Exports\CustomersExport;
use App\Http\Controllers\AdminWarrantySettingsController;
use App\Http\Controllers\WarrantyActivationController;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserDetail;
use App\Services\Warranty\MehrsoftWarrantyService;
use App\Services\Warranty\NationalCodeValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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
    Route::post('/logout', function (Request $request) {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::get('/dashboard', function () {
        $activeWarrantiesCount = 0;

        if (Schema::hasTable('warranties')) {
            $query = DB::table('warranties')
                ->where('activation_status', 'activated')
                ->where('mehrsoft_sync_status', 'synced')
                ->whereNull('deleted_at');

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
        Route::get('/activate', function (MehrsoftWarrantyService $warrantyService) {
            $user = auth()->user()->load(['addresses' => fn ($query) => $query->latest('is_default')->latest('id'), 'details']);
            $details = $user->details->pluck('value', 'key');

            return Inertia::render('Dashboard/Warranty/Activate', [
                'profile' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'mobile' => $user->mobile,
                    'national_code' => data_get($details->get('national_code'), 'value', ''),
                    'cust_type' => data_get($details->get('cust_type'), 'value', 0),
                    'cust_sex' => data_get($details->get('cust_sex'), 'value', 1),
                    'cust_name' => data_get($details->get('cust_name'), 'value', ''),
                    'state_code' => data_get($details->get('state_code'), 'value', ''),
                    'city_code' => data_get($details->get('city_code'), 'value', ''),
                ],
                'addresses' => $user->addresses->map(fn (UserAddress $address) => [
                    'id' => $address->id,
                    'title' => $address->title,
                    'province' => $address->province,
                    'city' => $address->city,
                    'address' => $address->address,
                    'is_default' => $address->is_default,
                ]),
                'cities' => $warrantyService->cities(),
                'citiesMeta' => $warrantyService->citiesMeta(),
                'activationSupport' => [
                    'phone' => session('activation_phone'),
                    'href' => session('activation_phone_href'),
                ],
            ]);
        })->name('activate');

        Route::post('/product-inquiry', [WarrantyActivationController::class, 'productInquiry'])
            ->name('product_inquiry');

        Route::post('/activate', [WarrantyActivationController::class, 'store'])
            ->name('activate.store');

        Route::get('/mine', function () {
            $warranties = collect();

            if (Schema::hasTable('warranties')) {
                $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->toIso8601String() : null;

                $warranties = DB::table('warranties')
                    ->where('user_id', auth()->id())
                    ->where('activation_status', 'activated')
                    ->where('mehrsoft_sync_status', 'synced')
                    ->whereNull('deleted_at')
                    ->latest('activated_at')
                    ->latest('id')
                    ->get()
                    ->map(fn ($warranty) => [
                        'id' => $warranty->id,
                        'product_serial' => $warranty->product_serial,
                        'product_code' => $warranty->product_code,
                        'warranty_type' => $warranty->warranty_type,
                        'warranty_period_months' => $warranty->warranty_period_months,
                        'activated_at' => $formatDate($warranty->activated_at),
                        'starts_at' => $formatDate($warranty->starts_at),
                        'expires_at' => $formatDate($warranty->expires_at),
                        'mehrsoft_sync_status' => $warranty->mehrsoft_sync_status,
                        'mehrsoft_document_no' => $warranty->mehrsoft_document_no,
                        'mehrsoft_fix_no' => $warranty->mehrsoft_fix_no,
                    ]);
            }

            return Inertia::render('Dashboard/Warranty/MyWarranties', [
                'warranties' => $warranties,
            ]);
        })->name('mine');
    });

    Route::get('/profile', function () {
        $user = auth()->user()->load(['addresses' => fn ($query) => $query->latest('is_default')->latest('id'), 'details']);
        $details = $user->details->pluck('value', 'key');

        return Inertia::render('Dashboard/Profile/Index', [
            'profile' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'mobile' => $user->mobile,
                'national_code' => data_get($details->get('national_code'), 'value', ''),
                'cust_type' => data_get($details->get('cust_type'), 'value', 0),
                'cust_sex' => data_get($details->get('cust_sex'), 'value', 1),
                'cust_name' => data_get($details->get('cust_name'), 'value', ''),
                'registered_at' => optional($user->registered_at)->toIso8601String(),
            ],
            'addresses' => $user->addresses->map(fn (UserAddress $address) => [
                'id' => $address->id,
                'title' => $address->title,
                'province' => $address->province,
                'city' => $address->city,
                'address' => $address->address,
                'is_default' => $address->is_default,
            ]),
        ]);
    })->name('profile.index');

    Route::put('/profile', function (Request $request, NationalCodeValidator $nationalCodeValidator) {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:80'],
            'last_name' => ['nullable', 'string', 'max:80'],
            'national_code' => ['nullable', 'string', 'max:10'],
            'cust_type' => ['nullable', Rule::in(['0', '1', 0, 1])],
            'cust_sex' => ['nullable', Rule::in(['0', '1', 0, 1])],
            'cust_name' => ['nullable', 'string', 'max:160'],
        ], [], [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'national_code' => 'کد ملی',
        ]);

        if (filled($validated['national_code'] ?? null) && ! $nationalCodeValidator->isValid($validated['national_code'])) {
            throw ValidationException::withMessages([
                'national_code' => 'کد ملی وارد شده معتبر نیست.',
            ]);
        }

        $request->user()->forceFill([
            'first_name' => $validated['first_name'] ?: null,
            'last_name' => $validated['last_name'] ?: null,
        ])->save();

        UserDetail::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'key' => 'national_code',
            ],
            [
                'value' => ['value' => $validated['national_code'] ?: null],
            ],
        );

        foreach (['cust_type', 'cust_sex', 'cust_name'] as $key) {
            UserDetail::query()->updateOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'key' => $key,
                ],
                [
                    'value' => ['value' => $validated[$key] ?? null],
                ],
            );
        }

        return back()->with('success', 'پروفایل به روز شد.');
    })->name('profile.update');

    Route::post('/profile/addresses', function (Request $request) {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:80'],
            'province' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:1000'],
            'is_default' => ['boolean'],
        ], [], [
            'title' => 'عنوان آدرس',
            'address' => 'آدرس',
        ]);

        $validated['is_default'] = (bool) ($validated['is_default'] ?? $request->user()->addresses()->doesntExist());

        if ($validated['is_default']) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $request->user()->addresses()->create($validated);

        return back()->with('success', 'آدرس ثبت شد.');
    })->name('profile.addresses.store');

    Route::put('/profile/addresses/{address}', function (Request $request, UserAddress $address) {
        abort_unless($address->user_id === $request->user()->id, 404);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:80'],
            'province' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:1000'],
            'is_default' => ['boolean'],
        ], [], [
            'title' => 'عنوان آدرس',
            'address' => 'آدرس',
        ]);

        $validated['is_default'] = (bool) ($validated['is_default'] ?? $address->is_default);

        if ($validated['is_default']) {
            $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('success', 'آدرس به روز شد.');
    })->name('profile.addresses.update');

    Route::delete('/profile/addresses/{address}', function (Request $request, UserAddress $address) {
        abort_unless($address->user_id === $request->user()->id, 404);

        $address->delete();

        return back()->with('success', 'آدرس حذف شد.');
    })->name('profile.addresses.destroy');

    Route::middleware('role_or_permission:general_manager|warranties.view_any')->group(function () {
        Route::get('/warranties', function (Request $request) {
            $validated = $request->validate([
                'search' => ['nullable', 'string', 'max:80'],
                'sort' => ['nullable', Rule::in(['id', 'product_serial', 'customer', 'activated_at', 'expires_at', 'mehrsoft_sync_status'])],
                'direction' => ['nullable', Rule::in(['asc', 'desc'])],
                'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
            ]);

            $search = trim((string) ($validated['search'] ?? ''));
            $sort = $validated['sort'] ?? 'activated_at';
            $direction = $validated['direction'] ?? 'desc';
            $perPage = (int) ($validated['per_page'] ?? 25);
            $formatDate = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->toIso8601String() : null;

            if (! Schema::hasTable('warranties')) {
                $warranties = new \Illuminate\Pagination\LengthAwarePaginator(
                    [],
                    0,
                    $perPage,
                    1,
                    ['path' => $request->url(), 'query' => $request->query()],
                );
            } else {
                $query = DB::table('warranties')
                    ->leftJoin('users', 'users.id', '=', 'warranties.user_id')
                    ->where('warranties.activation_status', 'activated')
                    ->where('warranties.mehrsoft_sync_status', 'synced')
                    ->whereNull('warranties.deleted_at')
                    ->select([
                        'warranties.id',
                        'warranties.product_serial',
                        'warranties.product_code',
                        'warranties.warranty_type',
                        'warranties.warranty_period_months',
                        'warranties.activated_at',
                        'warranties.starts_at',
                        'warranties.expires_at',
                        'warranties.mehrsoft_sync_status',
                        'warranties.mehrsoft_document_no',
                        'warranties.mehrsoft_fix_no',
                        'users.mobile as customer_mobile',
                        'users.first_name as customer_first_name',
                        'users.last_name as customer_last_name',
                    ]);

                if ($search !== '') {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('warranties.product_serial', 'like', "%{$search}%")
                            ->orWhere('warranties.product_code', 'like', "%{$search}%")
                            ->orWhere('warranties.warranty_type', 'like', "%{$search}%")
                            ->orWhere('warranties.mehrsoft_sync_status', 'like', "%{$search}%")
                            ->orWhere('users.mobile', 'like', "%{$search}%")
                            ->orWhere('users.first_name', 'like', "%{$search}%")
                            ->orWhere('users.last_name', 'like', "%{$search}%");
                    });
                }

                match ($sort) {
                    'id' => $query->orderBy('warranties.id', $direction),
                    'product_serial' => $query->orderBy('warranties.product_serial', $direction)->orderBy('warranties.id', 'desc'),
                    'customer' => $query->orderBy('users.last_name', $direction)->orderBy('users.first_name', $direction)->orderBy('warranties.id', 'desc'),
                    'expires_at' => $query->orderBy('warranties.expires_at', $direction)->orderBy('warranties.id', 'desc'),
                    'mehrsoft_sync_status' => $query->orderBy('warranties.mehrsoft_sync_status', $direction)->orderBy('warranties.id', 'desc'),
                    default => $query->orderBy('warranties.activated_at', $direction)->orderBy('warranties.id', 'desc'),
                };

                $warranties = $query
                    ->paginate($perPage)
                    ->withQueryString()
                    ->through(fn ($warranty) => [
                        'id' => $warranty->id,
                        'product_serial' => $warranty->product_serial,
                        'product_code' => $warranty->product_code,
                        'warranty_type' => $warranty->warranty_type,
                        'warranty_period_months' => $warranty->warranty_period_months,
                        'activated_at' => $formatDate($warranty->activated_at),
                        'starts_at' => $formatDate($warranty->starts_at),
                        'expires_at' => $formatDate($warranty->expires_at),
                        'mehrsoft_sync_status' => $warranty->mehrsoft_sync_status,
                        'mehrsoft_document_no' => $warranty->mehrsoft_document_no,
                        'mehrsoft_fix_no' => $warranty->mehrsoft_fix_no,
                        'customer_name' => trim(implode(' ', array_filter([$warranty->customer_first_name, $warranty->customer_last_name]))) ?: 'بدون نام',
                        'customer_mobile' => $warranty->customer_mobile,
                    ]);
            }

            return Inertia::render('Dashboard/Warranty/List', [
                'warranties' => $warranties,
                'filters' => [
                    'search' => $search,
                    'sort' => $sort,
                    'direction' => $direction,
                    'per_page' => $perPage,
                ],
            ]);
        })->name('admin.warranties.index');
    });

    Route::middleware('role_or_permission:general_manager|settings.manage')->group(function () {
        Route::get('/settings', function (MehrsoftWarrantyService $warrantyService) {
            $setting = fn (string $group, string $key): array => Schema::hasTable('app_settings')
                ? (AppSetting::query()->where('group', $group)->where('key', $key)->first()?->value ?? [])
                : [];

            $defaults = [
                'enabled' => true,
                'base_url' => 'https://www.mehrsofts.com/webservice/mehraccws.asmx',
                'financial_unit' => '',
                'username' => '',
                'timeout_seconds' => 30,
            ];
            $stored = $setting('accounting', 'connection');

            $smsDefaults = [
                'provider' => 'farazsms',
                'base_url' => config('services.farazsms.base_url', 'https://api.iranpayamak.com'),
                'line_number' => config('services.farazsms.line_number'),
                'otp_pattern_code' => config('services.farazsms.otp_pattern_code'),
                'otp_attribute' => config('services.farazsms.otp_attribute', 'code'),
                'number_format' => config('services.farazsms.number_format', 'english'),
                'timeout_seconds' => config('services.farazsms.timeout', 10),
            ];
            $smsStored = $setting('sms', 'farazsms');

            $securityDefaults = [
                'code_length' => config('otp.code_length', 6),
                'ttl_minutes' => config('otp.ttl_minutes', 2),
                'max_attempts' => config('otp.max_attempts', 5),
                'resend_seconds' => config('otp.resend_seconds', 60),
                'send_sms' => config('otp.send_sms', app()->isProduction()),
                'store_debug_code' => config('otp.store_debug_code', ! app()->isProduction()),
                'mobile_max_requests' => config('otp.rate_limits.mobile.max_attempts', 5),
                'mobile_decay_seconds' => config('otp.rate_limits.mobile.decay_seconds', 3600),
                'ip_max_requests' => config('otp.rate_limits.ip.max_attempts', 30),
                'ip_decay_seconds' => config('otp.rate_limits.ip.decay_seconds', 3600),
                'verify_mobile_max_attempts' => config('otp.verify_rate_limits.mobile.max_attempts', 10),
                'verify_mobile_decay_seconds' => config('otp.verify_rate_limits.mobile.decay_seconds', 600),
                'verify_ip_max_attempts' => config('otp.verify_rate_limits.ip.max_attempts', 60),
                'verify_ip_decay_seconds' => config('otp.verify_rate_limits.ip.decay_seconds', 3600),
            ];
            $securityStored = $setting('otp_security', 'limits');

            return Inertia::render('Dashboard/Settings/Index', [
                'accounting' => [
                    ...$defaults,
                    ...$stored,
                    'has_password' => filled($stored['password'] ?? null),
                    'cities' => $warrantyService->citiesMeta(),
                ],
                'warrantySettings' => [
                    'detail_good_full_code_rules' => $warrantyService->detailGoodFullCodeRules(),
                ],
                'sms' => [
                    ...$smsDefaults,
                    ...$smsStored,
                    'has_api_key' => filled($smsStored['api_key'] ?? config('services.farazsms.api_key')),
                ],
                'otpSecurity' => [
                    ...$securityDefaults,
                    ...$securityStored,
                ],
            ]);
        })->name('admin.settings.index');

        Route::put('/settings/accounting', function (Request $request) {
            $validated = $request->validate([
                'enabled' => ['boolean'],
                'base_url' => ['nullable', 'url', 'max:500'],
                'financial_unit' => ['nullable', 'integer', 'min:1'],
                'username' => ['nullable', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'max:255'],
                'timeout_seconds' => ['required', 'integer', 'min:5', 'max:120'],
            ], [], [
                'enabled' => 'وضعیت اتصال',
                'base_url' => 'آدرس وب سرویس حسابداری',
                'financial_unit' => 'کد سال مالی',
                'username' => 'نام کاربری',
                'password' => 'رمز عبور',
                'timeout_seconds' => 'زمان انتظار',
            ]);

            $current = AppSetting::query()
                ->where('group', 'accounting')
                ->where('key', 'connection')
                ->first()?->value ?? [];

            $payload = [
                'enabled' => (bool) ($validated['enabled'] ?? false),
                'base_url' => $validated['base_url'] ?? null,
                'financial_unit' => $validated['financial_unit'] ?? null,
                'username' => $validated['username'] ?? null,
                'password' => filled($validated['password'] ?? null)
                    ? $validated['password']
                    : ($current['password'] ?? null),
                'timeout_seconds' => (int) $validated['timeout_seconds'],
            ];

            AppSetting::query()->updateOrCreate(
                ['group' => 'accounting', 'key' => 'connection'],
                ['value' => $payload],
            );

            return back()->with('success', 'تنظیمات اتصال به حسابداری ذخیره شد.');
        })->name('admin.settings.accounting.update');

        Route::post('/settings/accounting/sync-cities', [AdminWarrantySettingsController::class, 'syncCities'])
            ->name('admin.settings.accounting.sync_cities');

        Route::put('/settings/warranty-rules', [AdminWarrantySettingsController::class, 'updateRules'])
            ->name('admin.settings.warranty_rules.update');

        Route::put('/settings/sms', function (Request $request) {
            $validated = $request->validate([
                'base_url' => ['required', 'url', 'max:500'],
                'api_key' => ['nullable', 'string', 'max:500'],
                'line_number' => ['required', 'string', 'max:80'],
                'otp_pattern_code' => ['required', 'string', 'max:120'],
                'otp_attribute' => ['required', 'string', 'max:80'],
                'number_format' => ['required', Rule::in(['english', 'persian'])],
                'timeout_seconds' => ['required', 'integer', 'min:3', 'max:60'],
            ], [], [
                'base_url' => 'آدرس پایه فراز اس ام اس',
                'api_key' => 'کلید API',
                'line_number' => 'شماره خط ارسال کننده',
                'otp_pattern_code' => 'کد پترن ورود',
                'otp_attribute' => 'نام متغیر کد ورود',
                'number_format' => 'فرمت عدد',
                'timeout_seconds' => 'زمان انتظار',
            ]);

            $current = AppSetting::query()
                ->where('group', 'sms')
                ->where('key', 'farazsms')
                ->first()?->value ?? [];

            AppSetting::query()->updateOrCreate(
                ['group' => 'sms', 'key' => 'farazsms'],
                ['value' => [
                    'provider' => 'farazsms',
                    'base_url' => $validated['base_url'],
                    'api_key' => filled($validated['api_key'] ?? null)
                        ? $validated['api_key']
                        : ($current['api_key'] ?? null),
                    'line_number' => $validated['line_number'],
                    'otp_pattern_code' => $validated['otp_pattern_code'],
                    'otp_attribute' => $validated['otp_attribute'],
                    'number_format' => $validated['number_format'],
                    'timeout_seconds' => (int) $validated['timeout_seconds'],
                ]],
            );

            return back()->with('success', 'تنظیمات پیامک ذخیره شد.');
        })->name('admin.settings.sms.update');

        Route::put('/settings/otp-security', function (Request $request) {
            $validated = $request->validate([
                'code_length' => ['required', 'integer', 'min:4', 'max:8'],
                'ttl_minutes' => ['required', 'integer', 'min:1', 'max:10'],
                'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
                'resend_seconds' => ['required', 'integer', 'min:30', 'max:600'],
                'send_sms' => ['boolean'],
                'store_debug_code' => ['boolean'],
                'mobile_max_requests' => ['required', 'integer', 'min:1', 'max:100'],
                'mobile_decay_seconds' => ['required', 'integer', 'min:60', 'max:86400'],
                'ip_max_requests' => ['required', 'integer', 'min:1', 'max:500'],
                'ip_decay_seconds' => ['required', 'integer', 'min:60', 'max:86400'],
                'verify_mobile_max_attempts' => ['required', 'integer', 'min:1', 'max:100'],
                'verify_mobile_decay_seconds' => ['required', 'integer', 'min:60', 'max:86400'],
                'verify_ip_max_attempts' => ['required', 'integer', 'min:1', 'max:500'],
                'verify_ip_decay_seconds' => ['required', 'integer', 'min:60', 'max:86400'],
            ], [], [
                'code_length' => 'طول کد ورود',
                'ttl_minutes' => 'مدت اعتبار کد',
                'max_attempts' => 'تعداد تلاش مجاز',
                'resend_seconds' => 'فاصله ارسال مجدد',
                'send_sms' => 'ارسال پیامک',
                'store_debug_code' => 'ذخیره کد تستی',
                'mobile_max_requests' => 'سقف درخواست موبایل',
                'mobile_decay_seconds' => 'بازه محدودیت موبایل',
                'ip_max_requests' => 'سقف درخواست IP',
                'ip_decay_seconds' => 'بازه محدودیت IP',
                'verify_mobile_max_attempts' => 'سقف تلاش تایید موبایل',
                'verify_mobile_decay_seconds' => 'بازه تایید موبایل',
                'verify_ip_max_attempts' => 'سقف تلاش تایید IP',
                'verify_ip_decay_seconds' => 'بازه تایید IP',
            ]);

            AppSetting::query()->updateOrCreate(
                ['group' => 'otp_security', 'key' => 'limits'],
                ['value' => [
                    ...$validated,
                    'send_sms' => (bool) ($validated['send_sms'] ?? false),
                    'store_debug_code' => (bool) ($validated['store_debug_code'] ?? false),
                ]],
            );

            return back()->with('success', 'تنظیمات امنیتی ورود ذخیره شد.');
        })->name('admin.settings.otp_security.update');
    });

    Route::middleware('role_or_permission:general_manager|users.view_any')->group(function () {
        Route::get('/users', function () {
            $users = User::query()
                ->with('roles:id,name,title')
                ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
                ->latest('id')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'name' => trim(implode(' ', array_filter([$user->first_name, $user->last_name]))) ?: 'بدون نام',
                    'mobile' => $user->mobile,
                    'roles' => $user->roles->pluck('name')->values()->all(),
                    'registered_at' => optional($user->registered_at)->toIso8601String(),
                    'created_at' => optional($user->created_at)->toIso8601String(),
                ]);

            $roles = Role::query()
                ->orderByRaw("FIELD(name, 'general_manager', 'customer') DESC")
                ->orderBy('name')
                ->get(['id', 'name', 'title'])
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'title' => $role->title,
                ]);

            return Inertia::render('Dashboard/Users/Index', [
                'users' => $users,
                'roles' => $roles,
            ]);
        })->name('admin.users.index');

        Route::put('/users/{user}', function (Request $request, User $user) {
            $validated = $request->validate([
                'first_name' => ['nullable', 'string', 'max:80'],
                'last_name' => ['nullable', 'string', 'max:80'],
                'mobile' => ['required', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($user->id)],
                'roles' => ['required', 'array', 'min:1'],
                'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            ], [], [
                'first_name' => 'نام',
                'last_name' => 'نام خانوادگی',
                'mobile' => 'موبایل',
                'roles' => 'نقش ها',
            ]);

            if ($user->is(auth()->user()) && $user->hasRole('general_manager') && ! in_array('general_manager', $validated['roles'], true)) {
                return back()->withErrors([
                    'roles' => 'برای جلوگیری از قطع دسترسی، نمی‌توانید نقش مدیر کل را از کاربر جاری حذف کنید.',
                ]);
            }

            $user->forceFill([
                'first_name' => $validated['first_name'] ?: null,
                'last_name' => $validated['last_name'] ?: null,
                'mobile' => $validated['mobile'],
            ])->save();

            $user->syncRoles($validated['roles']);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return back()->with('success', 'کاربر به روز شد.');
        })->name('admin.users.update');
    });

    Route::middleware('role_or_permission:general_manager|customers.view_any')->group(function () {
        Route::get('/customers', function (Request $request) {
            $validated = $request->validate([
                'search' => ['nullable', 'string', 'max:80'],
                'sort' => ['nullable', Rule::in(['id', 'mobile', 'name', 'registered_at'])],
                'direction' => ['nullable', Rule::in(['asc', 'desc'])],
                'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
            ]);

            $search = trim((string) ($validated['search'] ?? ''));
            $sort = $validated['sort'] ?? 'registered_at';
            $direction = $validated['direction'] ?? 'desc';
            $perPage = (int) ($validated['per_page'] ?? 25);

            $query = User::query()
                ->role('customer')
                ->with('roles:id,name');

            if ($search !== '') {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('mobile', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }

            match ($sort) {
                'id' => $query->orderBy('id', $direction),
                'mobile' => $query->orderBy('mobile', $direction)->orderBy('id', 'desc'),
                'name' => $query->orderBy('last_name', $direction)->orderBy('first_name', $direction)->orderBy('id', 'desc'),
                default => $query->orderBy('registered_at', $direction)->orderBy('id', 'desc'),
            };

            $customers = $query
                ->paginate($perPage)
                ->withQueryString()
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => trim(implode(' ', array_filter([$user->first_name, $user->last_name]))) ?: 'بدون نام',
                    'mobile' => $user->mobile,
                    'roles' => $user->roles->pluck('name')->values()->all(),
                    'registered_at' => optional($user->registered_at)->toIso8601String(),
                    'created_at' => optional($user->created_at)->toIso8601String(),
                ]);

            return Inertia::render('Dashboard/Customers/Index', [
                'customers' => $customers,
                'filters' => [
                    'search' => $search,
                    'sort' => $sort,
                    'direction' => $direction,
                    'per_page' => $perPage,
                ],
            ]);
        })->name('admin.customers.index');

        Route::get('/customers/export', function (Request $request) {
            $validated = $request->validate([
                'scope' => ['required', Rule::in(['all', 'current'])],
                'search' => ['nullable', 'string', 'max:80'],
                'sort' => ['nullable', Rule::in(['id', 'mobile', 'name', 'registered_at'])],
                'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            ]);

            $scope = $validated['scope'];
            $applyFilters = $scope === 'current';
            $filename = $scope === 'all'
                ? 'customers-all-'.now()->format('Y-m-d-His').'.xlsx'
                : 'customers-current-list-'.now()->format('Y-m-d-His').'.xlsx';

            return Excel::download(
                new CustomersExport($validated, $applyFilters),
                $filename,
            );
        })->name('admin.customers.export');
    });

    Route::middleware('role_or_permission:general_manager|roles.manage')->group(function () {
        Route::get('/roles', function () {
            $configuredGroups = collect(config('browatt_permissions.groups', []));
            $configuredPermissions = $configuredGroups
                ->flatMap(fn (array $group) => $group['permissions'] ?? [])
                ->pluck('name')
                ->filter()
                ->values();

            foreach ($configuredPermissions as $permission) {
                Permission::query()->firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $knownPermissions = $configuredPermissions->all();
            $customPermissions = Permission::query()
                ->whereNotIn('name', $knownPermissions)
                ->orderBy('name')
                ->get()
                ->map(fn (Permission $permission) => [
                    'name' => $permission->name,
                    'label' => $permission->name,
                    'description' => null,
                ])
                ->values();

            $permissionGroups = $configuredGroups
                ->map(fn (array $group) => [
                    'key' => $group['key'],
                    'label' => $group['label'],
                    'permissions' => collect($group['permissions'] ?? [])
                        ->map(fn (array $permission) => [
                            'name' => $permission['name'],
                            'label' => $permission['label'],
                            'description' => $permission['description'] ?? null,
                        ])
                        ->values()
                        ->all(),
                ])
                ->when($customPermissions->isNotEmpty(), fn ($groups) => $groups->push([
                    'key' => 'custom',
                    'label' => 'دسترسی های سفارشی',
                    'permissions' => $customPermissions->all(),
                ]))
                ->values();

            $roles = Role::query()
                ->with('permissions:id,name')
                ->withCount('users')
                ->orderByRaw("FIELD(name, 'general_manager', 'customer') DESC")
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'title' => $role->title,
                    'guard_name' => $role->guard_name,
                    'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
                    'users_count' => $role->users_count,
                    'is_system' => in_array($role->name, ['general_manager', 'customer'], true),
                ]);

            return Inertia::render('Dashboard/Roles/Index', [
                'roles' => $roles,
                'permissionGroups' => $permissionGroups,
            ]);
        })->name('admin.roles.index');

        Route::post('/roles', function (Request $request) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:120'],
                'name' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('roles', 'name')->where('guard_name', 'web')],
                'permissions' => ['array'],
                'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
            ], [], [
                'title' => 'عنوان نقش',
                'name' => 'نام نقش',
                'permissions' => 'دسترسی ها',
            ]);

            $role = Role::query()->create([
                'name' => $validated['name'],
                'title' => $validated['title'],
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($validated['permissions'] ?? []);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return back()->with('success', 'نقش جدید ثبت شد.');
        })->name('admin.roles.store');

        Route::put('/roles/{role}', function (Request $request, Role $role) {
            abort_unless($role->guard_name === 'web', 404);

            $isSystemRole = in_array($role->name, ['general_manager', 'customer'], true);

            $validated = $request->validate([
                'title' => ['required', 'string', 'max:120'],
                'name' => [
                    $isSystemRole ? 'sometimes' : 'required',
                    'string',
                    'max:80',
                    'alpha_dash',
                    Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role->id),
                ],
                'permissions' => ['array'],
                'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
            ], [], [
                'title' => 'عنوان نقش',
                'name' => 'نام نقش',
                'permissions' => 'دسترسی ها',
            ]);

            $role->forceFill(['title' => $validated['title']])->save();

            if (! $isSystemRole) {
                $role->forceFill(['name' => $validated['name']])->save();
            }

            $role->syncPermissions($validated['permissions'] ?? []);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return back()->with('success', 'نقش به روز شد.');
        })->name('admin.roles.update');
    });
});
