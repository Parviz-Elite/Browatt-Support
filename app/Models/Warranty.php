<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warranty extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_serial',
        'product_code',
        'product_name',
        'production_date',
        'warranty_type',
        'warranty_period_months',
        'activated_at',
        'starts_at',
        'expires_at',
        'activation_status',
        'cust_type',
        'cust_sex',
        'cust_name',
        'national_code',
        'state_code',
        'state_name',
        'city_code',
        'city_name',
        'address',
        'mehrsoft_sync_status',
        'mehrsoft_synced_at',
        'mehrsoft_document_no',
        'mehrsoft_fix_no',
        'mehrsoft_last_error',
        'mehrsoft_product_payload',
        'mehrsoft_save_payload',
        'mehrsoft_save_response',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'mehrsoft_synced_at' => 'datetime',
            'mehrsoft_product_payload' => 'array',
            'mehrsoft_save_payload' => 'array',
            'mehrsoft_save_response' => 'array',
        ];
    }
}
