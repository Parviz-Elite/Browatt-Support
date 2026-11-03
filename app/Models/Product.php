<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public const DEFAULT_WARRANTY_MONTHS = 18;

    public function scopeFinishedGoods(Builder $query): Builder
    {
        return $query->where('mehrsoft_good_type', 4);
    }

    public function effectiveWarrantyMonths(): int
    {
        return $this->warranty_months ?: self::DEFAULT_WARRANTY_MONTHS;
    }

    protected function casts(): array
    {
        return [
            'online_activation_enabled' => 'boolean',
            'warranty_months' => 'integer',
            'catalog_synced_at' => 'datetime',
            'warranty_checked_at' => 'datetime',
        ];
    }

    public static function normalizeCode(?string $code): string
    {
        return strtr(trim((string) $code), [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
