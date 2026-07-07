<?php

namespace App\Http\Controllers;

use App\Services\Warranty\MehrsoftWarrantyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class AdminWarrantySettingsController extends Controller
{
    public function __construct(
        private readonly MehrsoftWarrantyService $warranties,
    ) {}

    public function syncCities(): RedirectResponse
    {
        try {
            $this->warranties->syncCities();
        } catch (Throwable $exception) {
            return back()->with('error', 'همگام‌سازی استان و شهر انجام نشد: '.$exception->getMessage());
        }

        return back()->with('success', 'استان‌ها و شهرهای حسابداری همگام‌سازی شدند.');
    }

    public function updateRules(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.prefix' => ['required', 'string', 'max:40'],
            'rules.*.good_full_code' => ['required', 'string', 'max:80'],
        ], [], [
            'rules' => 'قوانین کد خدمات',
            'rules.*.prefix' => 'پیشوند کد کالا',
            'rules.*.good_full_code' => 'کد خدمات',
        ]);

        $rules = collect($validated['rules'])
            ->map(fn (array $rule) => [
                'prefix' => trim($rule['prefix']),
                'good_full_code' => trim($rule['good_full_code']),
            ])
            ->filter(fn (array $rule) => $rule['prefix'] !== '' && $rule['good_full_code'] !== '')
            ->values()
            ->all();

        if ($rules === []) {
            return back()->withErrors([
                'rules' => 'حداقل یک قانون معتبر باید ثبت شود.',
            ]);
        }

        $this->warranties->saveDetailGoodFullCodeRules($rules);

        return back()->with('success', 'قوانین کد خدمات گارانتی ذخیره شد.');
    }
}
