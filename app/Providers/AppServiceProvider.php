<?php

namespace App\Providers;

use App\Contracts\SmsProvider;
use App\Exceptions\OtpException;
use App\Services\Sms\FarazSmsProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
}
