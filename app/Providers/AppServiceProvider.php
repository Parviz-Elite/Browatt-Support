<?php

namespace App\Providers;

use App\Contracts\SmsProvider;
use App\Exceptions\OtpException;
use App\Models\AppSetting;
use App\Services\Sms\FarazSmsProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsProvider::class, function () {
            return match (config('otp.sms_provider')) {
                'farazsms' => $this->app->make(FarazSmsProvider::class),
                default => throw OtpException::smsProviderNotConfigured((string) config('otp.sms_provider')),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applyDatabaseSettings();

        RateLimiter::for('otp-requests', function (Request $request) {
            return [
                Limit::perMinute(3)->by('otp-request:ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('otp-verifications', function (Request $request) {
            return [
                Limit::perMinute(10)->by('otp-verify-route:ip:'.$request->ip()),
            ];
        });
    }

    private function applyDatabaseSettings(): void
    {
        try {
            if (! Schema::hasTable('app_settings')) {
                return;
            }

            $sms = AppSetting::query()
                ->where('group', 'sms')
                ->where('key', 'farazsms')
                ->first()?->value ?? [];

            if ($sms !== []) {
                config([
                    'services.farazsms.api_key' => $sms['api_key'] ?? config('services.farazsms.api_key'),
                    'services.farazsms.base_url' => $sms['base_url'] ?? config('services.farazsms.base_url'),
                    'services.farazsms.line_number' => $sms['line_number'] ?? config('services.farazsms.line_number'),
                    'services.farazsms.otp_pattern_code' => $sms['otp_pattern_code'] ?? config('services.farazsms.otp_pattern_code'),
                    'services.farazsms.otp_attribute' => $sms['otp_attribute'] ?? config('services.farazsms.otp_attribute'),
                    'services.farazsms.warranty_activation.enabled' => (bool) ($sms['warranty_activation_enabled'] ?? config('services.farazsms.warranty_activation.enabled', false)),
                    'services.farazsms.warranty_activation.pattern_code' => $sms['warranty_activation_pattern_code'] ?? config('services.farazsms.warranty_activation.pattern_code'),
                    'services.farazsms.warranty_activation.product_title_attribute' => $sms['warranty_activation_product_title_attribute'] ?? config('services.farazsms.warranty_activation.product_title_attribute', 'ptitle'),
                    'services.farazsms.warranty_activation.product_serial_attribute' => $sms['warranty_activation_product_serial_attribute'] ?? config('services.farazsms.warranty_activation.product_serial_attribute', 'pserial'),
                    'services.farazsms.warranty_activation.expires_at_attribute' => $sms['warranty_activation_expires_at_attribute'] ?? config('services.farazsms.warranty_activation.expires_at_attribute', 'wdate'),
                    'services.farazsms.number_format' => $sms['number_format'] ?? config('services.farazsms.number_format'),
                    'services.farazsms.timeout' => (int) ($sms['timeout_seconds'] ?? config('services.farazsms.timeout', 10)),
                ]);
            }

            $otp = AppSetting::query()
                ->where('group', 'otp_security')
                ->where('key', 'limits')
                ->first()?->value ?? [];

            if ($otp !== []) {
                config([
                    'otp.code_length' => (int) ($otp['code_length'] ?? config('otp.code_length', 6)),
                    'otp.ttl_minutes' => (int) ($otp['ttl_minutes'] ?? config('otp.ttl_minutes', 2)),
                    'otp.max_attempts' => (int) ($otp['max_attempts'] ?? config('otp.max_attempts', 5)),
                    'otp.resend_seconds' => (int) ($otp['resend_seconds'] ?? config('otp.resend_seconds', 60)),
                    'otp.send_sms' => (bool) ($otp['send_sms'] ?? config('otp.send_sms', app()->isProduction())),
                    'otp.store_debug_code' => (bool) ($otp['store_debug_code'] ?? config('otp.store_debug_code', ! app()->isProduction())),
                    'otp.rate_limits.mobile.max_attempts' => (int) ($otp['mobile_max_requests'] ?? config('otp.rate_limits.mobile.max_attempts', 5)),
                    'otp.rate_limits.mobile.decay_seconds' => (int) ($otp['mobile_decay_seconds'] ?? config('otp.rate_limits.mobile.decay_seconds', 3600)),
                    'otp.rate_limits.ip.max_attempts' => (int) ($otp['ip_max_requests'] ?? config('otp.rate_limits.ip.max_attempts', 30)),
                    'otp.rate_limits.ip.decay_seconds' => (int) ($otp['ip_decay_seconds'] ?? config('otp.rate_limits.ip.decay_seconds', 3600)),
                    'otp.verify_rate_limits.mobile.max_attempts' => (int) ($otp['verify_mobile_max_attempts'] ?? config('otp.verify_rate_limits.mobile.max_attempts', 10)),
                    'otp.verify_rate_limits.mobile.decay_seconds' => (int) ($otp['verify_mobile_decay_seconds'] ?? config('otp.verify_rate_limits.mobile.decay_seconds', 600)),
                    'otp.verify_rate_limits.ip.max_attempts' => (int) ($otp['verify_ip_max_attempts'] ?? config('otp.verify_rate_limits.ip.max_attempts', 60)),
                    'otp.verify_rate_limits.ip.decay_seconds' => (int) ($otp['verify_ip_decay_seconds'] ?? config('otp.verify_rate_limits.ip.decay_seconds', 3600)),
                ]);
            }

            $accounting = AppSetting::query()
                ->where('group', 'accounting')
                ->where('key', 'connection')
                ->first()?->value ?? [];

            if ($accounting !== []) {
                $baseUrl = (string) ($accounting['base_url'] ?? '');
                $wsdlUrl = str_contains(strtolower($baseUrl), '?wsdl')
                    ? $baseUrl
                    : rtrim($baseUrl, '?').'?WSDL';

                config([
                    'mehrsoftintegration.enabled' => (bool) ($accounting['enabled'] ?? config('mehrsoftintegration.enabled', false)),
                    'mehrsoftintegration.wsdl_url' => $baseUrl !== '' ? $wsdlUrl : config('mehrsoftintegration.wsdl_url'),
                    'mehrsoftintegration.financial_unit' => (int) ($accounting['financial_unit'] ?? config('mehrsoftintegration.financial_unit', 0)),
                    'mehrsoftintegration.username' => $accounting['username'] ?? config('mehrsoftintegration.username'),
                    'mehrsoftintegration.password' => $accounting['password'] ?? config('mehrsoftintegration.password'),
                    'mehrsoftintegration.soap.connection_timeout' => (int) ($accounting['timeout_seconds'] ?? config('mehrsoftintegration.soap.connection_timeout', 20)),
                ]);
            }
        } catch (Throwable) {
            return;
        }
    }
}
