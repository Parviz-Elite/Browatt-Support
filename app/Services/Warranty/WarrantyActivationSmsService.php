<?php

namespace App\Services\Warranty;

use App\Contracts\SmsProvider;
use App\Models\Warranty;
use Hekmatinasser\Verta\Verta;
use RuntimeException;

class WarrantyActivationSmsService
{
    public function __construct(
        private readonly SmsProvider $smsProvider,
    ) {}

    public function send(Warranty $warranty): void
    {
        if (! config('services.farazsms.warranty_activation.enabled', false)) {
            return;
        }

        $warranty->loadMissing('user:id,mobile');

        if (
            $warranty->activation_status !== 'activated'
            || $warranty->mehrsoft_sync_status !== 'synced'
            || blank($warranty->user?->mobile)
        ) {
            return;
        }

        $config = config('services.farazsms.warranty_activation', []);
        $patternCode = (string) ($config['pattern_code'] ?? '');
        $productTitleAttribute = (string) ($config['product_title_attribute'] ?? 'ptitle');
        $productSerialAttribute = (string) ($config['product_serial_attribute'] ?? 'pserial');
        $expiresAtAttribute = (string) ($config['expires_at_attribute'] ?? 'wdate');

        if (
            blank($patternCode)
            || blank($productTitleAttribute)
            || blank($productSerialAttribute)
            || blank($expiresAtAttribute)
            || $warranty->expires_at === null
        ) {
            throw new RuntimeException('Warranty activation SMS pattern is not configured.');
        }

        $this->smsProvider->sendPattern((string) $warranty->user->mobile, $patternCode, [
            $productTitleAttribute => (string) ($warranty->product_name ?: 'محصول بروات'),
            $productSerialAttribute => (string) $warranty->product_serial,
            $expiresAtAttribute => Verta::instance($warranty->expires_at)->format('Y/m/d'),
        ]);
    }
}
