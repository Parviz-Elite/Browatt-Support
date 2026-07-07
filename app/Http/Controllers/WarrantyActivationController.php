<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Services\Warranty\MehrsoftWarrantyService;
use App\Services\Warranty\NationalCodeValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class WarrantyActivationController extends Controller
{
    public function __construct(
        private readonly MehrsoftWarrantyService $warranties,
        private readonly NationalCodeValidator $nationalCodes,
    ) {}

    public function productInquiry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial' => ['required', 'string', 'max:120'],
        ], [], [
            'serial' => 'شماره سریال',
        ]);

        try {
            $result = $this->warranties->inquire($request->user(), $validated['serial']);
            Cache::forget($this->invalidSerialCacheKey($request));

            return response()->json([
                'warranty_id' => $result['warranty']->id,
                'product' => $result['product'],
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            if ($exception->getMessage() !== 'invalid_serial') {
                throw $exception;
            }

            $cacheKey = $this->invalidSerialCacheKey($request);
            Cache::add($cacheKey, 0, now()->addMinutes(30));
            $attempts = (int) Cache::increment($cacheKey);
            Cache::put($cacheKey, $attempts, now()->addMinutes(30));

            $message = $attempts > 2
                ? 'کاربر گرامی، لطفا از صحت سریال اطمینان حاصل نمایید و یا جهت راهنمایی با شماره 02191693797 تماس حاصل نمایید.'
                : 'کاربر گرامی، سریال وارد شده نامعتبر می باشد.';

            return response()->json([
                'message' => $message,
                'phone' => $attempts > 2 ? '02191693797' : null,
                'phone_href' => $attempts > 2 ? 'tel:02191693797' : null,
                'errors' => ['serial' => [$message]],
            ], 422);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warranty_id' => ['required', 'integer', Rule::exists('warranties', 'id')],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'cust_type' => ['required', Rule::in(['0', '1', 0, 1])],
            'cust_sex' => ['required', Rule::in(['0', '1', 0, 1])],
            'cust_name' => ['nullable', 'required_if:cust_type,1', 'string', 'max:160'],
            'national_code' => ['required', 'string', 'max:10'],
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('user_addresses', 'id')
                    ->where(fn ($query) => $query->where('user_id', $request->user()->id)->whereNull('deleted_at')),
            ],
            'address_title' => ['nullable', 'string', 'max:80'],
            'state_code' => ['required', 'string', 'max:80'],
            'city_code' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:1000'],
        ], [], [
            'warranty_id' => 'استعلام محصول',
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'cust_type' => 'نوع مشتری',
            'cust_sex' => 'جنسیت',
            'cust_name' => 'نام شخص حقوقی',
            'national_code' => 'کد ملی',
            'address_id' => 'آدرس',
            'address_title' => 'عنوان آدرس',
            'state_code' => 'استان',
            'city_code' => 'شهر',
            'address' => 'آدرس',
        ]);

        if (! $this->nationalCodes->isValid($validated['national_code'])) {
            return back()->withErrors([
                'national_code' => 'کد ملی وارد شده معتبر نیست.',
            ])->withInput();
        }

        $warranty = Warranty::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($validated['warranty_id'])
            ->firstOrFail();

        abort_unless($warranty->activation_status === 'inquired', 404);

        $warranty = $this->warranties->activate($request->user(), $validated);

        if ($warranty->mehrsoft_sync_status !== 'synced') {
            $cacheKey = $this->activationFailureCacheKey($request, $warranty->id);
            Cache::add($cacheKey, 0, now()->addMinutes(30));
            $attempts = (int) Cache::increment($cacheKey);
            Cache::put($cacheKey, $attempts, now()->addMinutes(30));

            $message = $attempts > 2
                ? 'کاربر گرامی، ثبت گارانتی انجام نشد. لطفا دوباره تلاش کنید و یا جهت راهنمایی با شماره 02191693797 تماس حاصل نمایید.'
                : 'ثبت گارانتی انجام نشد. لطفا دوباره تلاش کنید.';

            return back()
                ->withErrors(['activation' => $message])
                ->with('activation_phone', $attempts > 2 ? '02191693797' : null)
                ->with('activation_phone_href', $attempts > 2 ? 'tel:02191693797' : null)
                ->withInput();
        }

        Cache::forget($this->activationFailureCacheKey($request, $warranty->id));

        return redirect()->route('warranties.mine')->with('success', 'گارانتی با موفقیت فعال شد.');
    }

    private function invalidSerialCacheKey(Request $request): string
    {
        return 'warranty-invalid-serial:'.$request->user()->id.':'.$request->ip();
    }

    private function activationFailureCacheKey(Request $request, int $warrantyId): string
    {
        return 'warranty-activation-failed:'.$request->user()->id.':'.$warrantyId.':'.$request->ip();
    }
}
