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
            $payload = $this->mehrsoft->getGoods();
        } finally {
            $this->mehrsoft->logout();
        }

        $rows = $payload['Goods'] ?? $payload['StockGoods'] ?? $payload['Table'] ?? $payload;
        $now = now();
        $products = [];

        if (! is_array($rows) || ! array_is_list($rows) || $rows === []) {
            throw new UnexpectedValueException('MehrSoft did not return a recognizable goods list.');
        }

        foreach ($rows as $row) {
            $fields = array_change_key_case(is_array($row) ? $row : []);
            $rawCode = $fields['goodfullcode'] ?? $fields['fullcode'] ?? null;
            $rawName = $fields['goodname'] ?? $fields['name'] ?? null;
            $code = is_scalar($rawCode) ? Product::normalizeCode((string) $rawCode) : '';
            $name = is_string($rawName) ? trim($rawName) : '';

            if ($code === '' || strlen($code) > 80 || $name === '' || mb_strlen($name) > 255) {
                throw new UnexpectedValueException('MehrSoft returned an invalid goods record.');
            }

            $products[$code] = [
                'code' => $code,
                'name' => $name,
                'catalog_synced_at' => $now,
            ];
        }

        DB::transaction(function () use ($products) {
            foreach (array_chunk($products, 500) as $chunk) {
                Product::query()->upsert($chunk, ['code'], ['name', 'catalog_synced_at']);
            }
        });
    }

    public function syncWarrantyBatch(int $after): array
    {
        $products = Product::query()
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
