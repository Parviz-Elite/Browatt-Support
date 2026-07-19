<?php

namespace App\Services\Warranty;

use App\Models\AppSetting;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserDetail;
use App\Models\Warranty;
use Browatt\MehrsoftIntegration\Contracts\MehrsoftClient;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class MehrsoftWarrantyService
{
    public function __construct(
        private readonly MehrsoftClient $mehrsoft,
    ) {}

    /**
     * @return array{warranty: Warranty, product: array<string, mixed>}
     */
    public function inquire(User $user, string $serial): array
    {
        $serial = trim($serial);
        try {
            $payload = $this->mehrsoft->getProductStatusBySerial($serial);
        } finally {
            $this->mehrsoft->logout();
        }

        if ($this->hasExistingAfterSales($payload)) {
            abort(422, 'این سریال قبلا ثبت شده است.');
        }

        if (! $this->hasProductData($payload)) {
            abort(422, 'invalid_serial');
        }

        $product = $this->extractProduct($payload);

        $warranty = Warranty::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'product_serial' => $serial,
            ],
            [
                'product_code' => $product['good_full_code'],
                'product_name' => $product['name'],
                'production_date' => $product['production_date'],
                'warranty_type' => '1',
                'warranty_period_months' => 18,
                'activation_status' => 'inquired',
                'mehrsoft_sync_status' => 'not_sent',
                'mehrsoft_last_error' => null,
                'mehrsoft_product_payload' => $payload,
            ],
        );

        return [
            'warranty' => $warranty,
            'product' => $this->presentProduct($warranty),
        ];
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function activate(User $user, array $validated): Warranty
    {
        $warranty = Warranty::query()
            ->where('user_id', $user->id)
            ->whereKey($validated['warranty_id'])
            ->where('activation_status', 'inquired')
            ->firstOrFail();

        $state = $this->findCity((string) $validated['state_code']);
        $city = $this->findCity((string) $validated['city_code']);

        if ($state === null || $city === null || (string) ($city['parent_code'] ?? '') !== (string) ($state['code'] ?? '')) {
            abort(422, 'استان یا شهر انتخاب شده معتبر نیست.');
        }

        $now = now();
        $description = trim($validated['first_name'].' '.$validated['last_name']).' - ثبت گارانتی از سایت';
        $payload = $this->buildSavePayload($warranty, $user, $validated, $state, $city, $description, $now);

        return DB::transaction(function () use ($user, $validated, $warranty, $payload, $state, $city, $description, $now) {
            $this->storeCustomerProfile($user, $validated);
            $this->storeAddress($user, $validated, $state, $city);

            $warranty->forceFill([
                'activated_at' => null,
                'starts_at' => null,
                'expires_at' => null,
                'activation_status' => 'inquired',
                'cust_type' => (int) $validated['cust_type'],
                'cust_sex' => (int) $validated['cust_sex'],
                'cust_name' => $validated['cust_name'] ?? null,
                'national_code' => $validated['national_code'],
                'state_code' => $state['code'],
                'state_name' => $state['name'],
                'city_code' => $city['code'],
                'city_name' => $city['name'],
                'address' => $validated['address'],
                'mehrsoft_sync_status' => 'pending',
                'mehrsoft_save_payload' => $payload,
                'mehrsoft_last_error' => null,
            ])->save();

            try {
                $response = $this->mehrsoft->saveAfterSales($payload);

                $warranty->forceFill([
                    'activated_at' => $now,
                    'starts_at' => $now,
                    'expires_at' => $now->copy()->addMonths(18),
                    'activation_status' => 'activated',
                    'mehrsoft_sync_status' => 'synced',
                    'mehrsoft_synced_at' => now(),
                    'mehrsoft_document_no' => $this->firstScalar($response, ['No', 'DocNo', 'DocumentNo', 'AssNo']),
                    'mehrsoft_fix_no' => $this->firstScalar($response, ['FixNo', 'AssFixNo']) ?: (string) $warranty->id,
                    'mehrsoft_save_response' => $response,
                    'mehrsoft_last_error' => null,
                ])->save();
            } catch (Throwable $exception) {
                $warranty->forceFill([
                    'mehrsoft_sync_status' => 'failed',
                    'mehrsoft_last_error' => $exception->getMessage(),
                ])->save();
            } finally {
                $this->mehrsoft->logout();
            }

            return $warranty->refresh();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function syncCities(): array
    {
        try {
            $response = $this->mehrsoft->getAccCities();
            $items = $this->normalizeCities($response);

            AppSetting::query()->updateOrCreate(
                ['group' => 'accounting', 'key' => 'cities'],
                ['value' => [
                    'items' => $items,
                    'synced_at' => now()->toIso8601String(),
                    'last_error' => null,
                ]],
            );

            return $items;
        } catch (Throwable $exception) {
            $current = $this->citiesSetting();

            AppSetting::query()->updateOrCreate(
                ['group' => 'accounting', 'key' => 'cities'],
                ['value' => [
                    'items' => $current['items'] ?? [],
                    'synced_at' => $current['synced_at'] ?? null,
                    'last_error' => $exception->getMessage(),
                ]],
            );

            throw $exception;
        } finally {
            $this->mehrsoft->logout();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function cities(): array
    {
        return $this->normalizeCityHierarchy($this->citiesSetting()['items'] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function citiesMeta(): array
    {
        $setting = $this->citiesSetting();
        $syncedAt = $setting['synced_at'] ?? null;

        return [
            'synced_at' => $syncedAt,
            'synced_at_label' => $this->formatJalaliDateTime($syncedAt),
            'last_error' => $setting['last_error'] ?? null,
            'count' => count($this->normalizeCityHierarchy($setting['items'] ?? [])),
        ];
    }

    /**
     * @return array<int, array{prefix: string, good_full_code: string}>
     */
    public function detailGoodFullCodeRules(): array
    {
        $stored = AppSetting::query()
            ->where('group', 'warranty')
            ->where('key', 'detail_good_full_code_rules')
            ->first()?->value;

        return is_array($stored) && $stored !== []
            ? array_values($stored)
            : $this->defaultDetailGoodFullCodeRules();
    }

    /**
     * @param array<int, array{prefix: string, good_full_code: string}> $rules
     */
    public function saveDetailGoodFullCodeRules(array $rules): void
    {
        AppSetting::query()->updateOrCreate(
            ['group' => 'warranty', 'key' => 'detail_good_full_code_rules'],
            ['value' => array_values($rules)],
        );
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, synced_at?: string|null, last_error?: string|null}
     */
    private function citiesSetting(): array
    {
        return AppSetting::query()
            ->where('group', 'accounting')
            ->where('key', 'cities')
            ->first()?->value ?? ['items' => []];
    }

    private function formatJalaliDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Verta::instance(Carbon::parse($value))->format('Y/m/d H:i');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findCity(string $code): ?array
    {
        return collect($this->cities())
            ->first(fn (array $item) => (string) ($item['code'] ?? '') === $code);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function hasExistingAfterSales(array $payload): bool
    {
        return count($this->listValue($payload, 'AfterSaleService')) > 0;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function hasProductData(array $payload): bool
    {
        return count($this->listValue($payload, 'ProductInfo')) > 0
            || count($this->listValue($payload, 'ProductDetails')) > 0;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array{name: string|null, good_full_code: string|null, production_date: string|null}
     */
    private function extractProduct(array $payload): array
    {
        $details = $this->productDetailsMap($payload);
        $productInfo = $this->listValue($payload, 'ProductInfo')[0] ?? [];

        return [
            'name' => $details['نام محصول']
                ?? $this->firstScalar($productInfo, ['GoodName', 'Name', 'Title', 'ProductName']),
            'good_full_code' => $details['کد محصول']
                ?? $this->firstScalar($productInfo, ['GoodFullCode', 'FullCode', 'GoodCode', 'Code']),
            'production_date' => $this->normalizeMehrsoftDate(
                $details['تاریخ ثبت']
                    ?? $this->firstScalar($productInfo, ['ProductionDate', 'ProductDate', 'Date'])
            ),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, string>
     */
    private function productDetailsMap(array $payload): array
    {
        $details = [];

        foreach ($this->listValue($payload, 'ProductDetails') as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = $this->firstScalar($row, ['PiName', 'Name', 'Title']);
            $value = $this->firstScalar($row, ['PiValue', 'Value']);

            if ($name !== null && $value !== null) {
                $details[$name] = $value;
            }
        }

        return $details;
    }

    /**
     * @return array<string, mixed>
     */
    private function presentProduct(Warranty $warranty): array
    {
        return [
            'id' => $warranty->id,
            'serial' => $warranty->product_serial,
            'name' => $warranty->product_name,
            'good_full_code' => $warranty->product_code,
            'production_date' => $warranty->production_date,
        ];
    }

    /**
     * @param array<string, mixed> $validated
     * @param array<string, mixed> $state
     * @param array<string, mixed> $city
     *
     * @return array<string, mixed>
     */
    private function buildSavePayload(Warranty $warranty, User $user, array $validated, array $state, array $city, string $description, Carbon $now): array
    {
        $verta = Verta::instance($now);
        $productionDate = $this->normalizeMehrsoftDate($warranty->production_date);

        return [
            'ActionType' => 'Insert',
            'EntityType' => 'StrAfterSalesServices',
            'No' => '',
            'FixNo' => (string) $warranty->id,
            'Date' => $verta->format('Ymd'),
            'Time' => $now->format('His'),
            'Desc' => $description,
            'Flag' => 0,
            'Kol' => 320,
            'Moein' => 15,
            'Tafsil' => 29001,
            'Tafsil2' => '',
            'Tafsil3' => '',
            'TypeTitle' => '',
            'AfterSales' => [
                'GoodSerial' => $warranty->product_serial,
                'GoodFullCode' => $warranty->product_code,
                'ProductionDate' => $productionDate,
                'WarrantyType' => 1,
                'WarrantyPeriodMonth' => 18,
                'CustType' => (string) $validated['cust_type'],
                'CustSex' => (string) $validated['cust_sex'],
                'CustFirstName' => $validated['first_name'],
                'CustLastName' => $validated['last_name'],
                'CustName' => $validated['cust_name'] ?? '',
                'CustNationalCode' => $validated['national_code'],
                'CustState' => $state['code'],
                'CustCity' => $city['code'],
                'CustAddress' => $validated['address'],
                'CustPhone' => $user->mobile,
                'AfterSalesDesc' => $description,
            ],
            'Details' => [
                [
                    'StdGsId' => 13,
                    'GoodFullCode' => $this->mapDetailGoodFullCode((string) $warranty->product_code),
                    'StdCount' => 1,
                    'StdPrice' => 0,
                    'StdPaidWithCustomer' => 0,
                    'StdDesc' => $description,
                    'StdRequestStNo' => '',
                ],
            ],
        ];
    }

    private function mapDetailGoodFullCode(string $productCode): string
    {
        foreach ($this->detailGoodFullCodeRules() as $rule) {
            $prefix = (string) ($rule['prefix'] ?? '');

            if ($prefix !== '' && str_starts_with($productCode, $prefix)) {
                return (string) ($rule['good_full_code'] ?? '');
            }
        }

        return '';
    }

    private function normalizeMehrsoftDate(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits !== '' ? $digits : trim($value);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function storeCustomerProfile(User $user, array $validated): void
    {
        $user->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
        ])->save();

        UserDetail::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'national_code'],
            ['value' => ['value' => $validated['national_code']]],
        );

        UserDetail::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'cust_type'],
            ['value' => ['value' => (int) $validated['cust_type']]],
        );

        UserDetail::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'cust_sex'],
            ['value' => ['value' => (int) $validated['cust_sex']]],
        );

        UserDetail::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'cust_name'],
            ['value' => ['value' => $validated['cust_name'] ?? null]],
        );

        UserDetail::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'state_code'],
            ['value' => ['value' => $validated['state_code']]],
        );

        UserDetail::query()->updateOrCreate(
            ['user_id' => $user->id, 'key' => 'city_code'],
            ['value' => ['value' => $validated['city_code']]],
        );
    }

    /**
     * @param array<string, mixed> $validated
     * @param array<string, mixed> $state
     * @param array<string, mixed> $city
     */
    private function storeAddress(User $user, array $validated, array $state, array $city): UserAddress
    {
        $address = filled($validated['address_id'] ?? null)
            ? $user->addresses()->whereKey($validated['address_id'])->first()
            : null;

        if ($address === null) {
            $user->addresses()->update(['is_default' => false]);

            return $user->addresses()->create([
                'title' => $validated['address_title'] ?? null,
                'province' => $state['name'],
                'city' => $city['name'],
                'address' => $validated['address'],
                'is_default' => true,
            ]);
        }

        $user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);

        $address->update([
            'title' => $validated['address_title'] ?? $address->title,
            'province' => $state['name'],
            'city' => $city['name'],
            'address' => $validated['address'],
            'is_default' => true,
        ]);

        return $address;
    }

    /**
     * @param array<int|string, mixed> $response
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeCities(array $response): array
    {
        $items = [];
        $this->walkCities($response, $items);

        return collect($items)
            ->filter(fn (array $item) => ($item['code'] ?? '') !== '' && ($item['name'] ?? '') !== '')
            ->unique('code')
            ->values()
            ->pipe(fn ($items) => $this->normalizeCityHierarchy($items->all()));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeCityHierarchy(array $items): array
    {
        $normalized = collect($items)
            ->map(fn (array $item) => [
                'code' => (string) ($item['code'] ?? ''),
                'name' => (string) ($item['name'] ?? ''),
                'parent_code' => filled($item['parent_code'] ?? null)
                    ? (string) $item['parent_code']
                    : null,
                'parent_name' => filled($item['parent_name'] ?? null)
                    ? (string) $item['parent_name']
                    : null,
            ])
            ->filter(fn (array $item) => $item['code'] !== '' && $item['name'] !== '')
            ->values();

        $states = $normalized
            ->filter(fn (array $item) => $this->isStateCityItem($item))
            ->map(fn (array $item) => [
                ...$item,
                'name' => preg_replace('/^استان\s+/u', '', $item['name']) ?: $item['name'],
                'parent_code' => null,
                'parent_name' => null,
            ])
            ->unique('code')
            ->values();

        $statesByPrefix = $states->keyBy(fn (array $item) => substr($item['code'], 0, 2));

        $cities = $normalized
            ->reject(fn (array $item) => $this->isStateCityItem($item))
            ->map(function (array $item) use ($statesByPrefix) {
                if (($item['parent_code'] ?? null) !== null) {
                    return $item;
                }

                $state = $statesByPrefix->get(substr($item['code'], 0, 2));

                return [
                    ...$item,
                    'parent_code' => $state['code'] ?? null,
                    'parent_name' => $state['name'] ?? null,
                ];
            })
            ->filter(fn (array $item) => ($item['parent_code'] ?? null) !== null)
            ->unique('code')
            ->values();

        return $states->concat($cities)->values()->all();
    }

    /**
     * @param array<string, mixed> $item
     */
    private function isStateCityItem(array $item): bool
    {
        $code = (string) ($item['code'] ?? '');

        return preg_match('/^\d{2}00000$/', $code) === 1;
    }

    /**
     * @param array<int|string, mixed> $nodes
     * @param array<int, array<string, mixed>> $items
     */
    private function walkCities(array $nodes, array &$items, ?array $parent = null): void
    {
        if (! array_is_list($nodes)) {
            $nodes = [$nodes];
        }

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $code = $this->firstScalar($node, ['Code', 'Id', 'ID', 'AccCode', 'CityCode', 'StateCode']);
            $name = $this->firstScalar($node, ['Name', 'Title', 'CityName', 'StateName']);

            $current = [
                'code' => (string) $code,
                'name' => (string) $name,
                'parent_code' => $parent['code'] ?? null,
                'parent_name' => $parent['name'] ?? null,
            ];

            if ($current['code'] !== '' || $current['name'] !== '') {
                $items[] = $current;
            }

            foreach (['Children', 'Childs', 'Items', 'Cities', 'SubItems'] as $childrenKey) {
                $children = Arr::get($node, $childrenKey);

                if (is_array($children)) {
                    $this->walkCities($children, $items, $current);
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $keys
     */
    private function firstScalar(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($data, $key);

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<int, array<string, mixed>>
     */
    private function listValue(array $payload, string $key): array
    {
        $value = Arr::get($payload, $key, []);

        if (! is_array($value)) {
            return [];
        }

        if ($value === [] || array_is_list($value)) {
            return $value;
        }

        return [$value];
    }

    /**
     * @return array<int, array{prefix: string, good_full_code: string}>
     */
    private function defaultDetailGoodFullCodeRules(): array
    {
        return [
            ['prefix' => '3001', 'good_full_code' => '7001001'],
            ['prefix' => '3003', 'good_full_code' => '7001002'],
            ['prefix' => '3004', 'good_full_code' => '7001003'],
        ];
    }
}
