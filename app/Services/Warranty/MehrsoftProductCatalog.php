<?php

namespace App\Services\Warranty;

use App\Models\Product;
use Browatt\MehrsoftIntegration\Contracts\MehrsoftClient;
use Illuminate\Support\Facades\DB;
use UnexpectedValueException;

class MehrsoftProductCatalog
{
    public function __construct(private readonly MehrsoftClient $mehrsoft) {}

    public function syncGoods(): void
    {
        try {
            $rows = $this->mehrsoft->getGoods();
        } finally {
            $this->mehrsoft->logout();
        }

        $now = now();
        $products = [];

        if (! array_is_list($rows) || $rows === []) {
            throw new UnexpectedValueException('MehrSoft did not return a recognizable goods list.');
        }

        foreach ($rows as $row) {
            $fields = array_change_key_case(is_array($row) ? $row : []);
            $rawCode = $fields['goodfullcode'] ?? $fields['fullcode'] ?? null;
            $rawName = $fields['goodname'] ?? $fields['name'] ?? null;
            $type = filter_var($fields['goodtype'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 255],
            ]);
            $code = is_scalar($rawCode) ? Product::normalizeCode((string) $rawCode) : '';
            $name = is_string($rawName) ? trim($rawName) : '';

            if ($code === '' || strlen($code) > 80 || $name === '' || mb_strlen($name) > 255 || $type === false) {
                throw new UnexpectedValueException('MehrSoft returned an invalid goods record.');
            }

            $products[$code] = [
                'code' => $code,
                'name' => $name,
                'mehrsoft_good_type' => $type,
                'catalog_synced_at' => $now,
            ];
        }

        DB::transaction(function () use ($products) {
            foreach (array_chunk($products, 500) as $chunk) {
                Product::query()->upsert($chunk, ['code'], ['name', 'mehrsoft_good_type', 'catalog_synced_at']);
            }

            Product::query()->finishedGoods()
                ->whereNotIn('code', array_column($products, 'code'))
                ->update(['mehrsoft_good_type' => null]);
        });
    }

    public function syncWarrantyBatch(int $after): array
    {
        $products = Product::query()->finishedGoods()
            ->whereNotNull('catalog_synced_at')
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(4)
            ->get();

        $batch = $products->take(3);

        try {
            foreach ($batch as $product) {
                $months = $this->mehrsoft->getWarrantyMonths('1', $product->code);

                if ($months !== null && ($months < 0 || $months > 1200)) {
                    throw new UnexpectedValueException('MehrSoft returned an invalid warranty period.');
                }

                $product->forceFill([
                    'warranty_months' => $months,
                    'warranty_checked_at' => now(),
                ])->save();
            }
        } finally {
            $this->mehrsoft->logout();
        }

        return [
            'checked' => $batch->count(),
            'next' => $products->count() > $batch->count() ? $batch->last()->id : null,
        ];
    }
}
