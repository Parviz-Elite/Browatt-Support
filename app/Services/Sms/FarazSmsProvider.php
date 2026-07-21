<?php

namespace App\Services\Sms;

use App\Contracts\SmsProvider;
use App\Exceptions\OtpException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class FarazSmsProvider implements SmsProvider
{
    public function sendOtp(string $mobile, string $code): void
    {
        $config = config('services.farazsms');

        foreach (['api_key', 'base_url', 'line_number', 'otp_pattern_code'] as $key) {
            if (blank($config[$key] ?? null)) {
                throw OtpException::smsProviderNotConfigured('farazsms');
            }
        }

        $this->sendPattern($mobile, (string) $config['otp_pattern_code'], [
            (string) ($config['otp_attribute'] ?? 'code') => $code,
        ]);
    }

    /**
     * @param  array<string, string>  $attributes
     */
    public function sendPattern(string $mobile, string $patternCode, array $attributes): void
    {
        $config = config('services.farazsms');

        foreach (['api_key', 'base_url', 'line_number'] as $key) {
            if (blank($config[$key] ?? null)) {
                throw new RuntimeException("FarazSMS configuration [{$key}] is missing.");
            }
        }

        if (blank($patternCode)) {
            throw new RuntimeException('FarazSMS pattern code is missing.');
        }

        try {
            Http::baseUrl(rtrim((string) $config['base_url'], '/'))
                ->withHeaders([
                    'Api-Key' => (string) $config['api_key'],
                ])
                ->acceptJson()
                ->asJson()
                ->timeout((int) ($config['timeout'] ?? 10))
                ->post('/ws/v1/sms/pattern', [
                    'code' => $patternCode,
                    'attributes' => $attributes,
                    'recipient' => $mobile,
                    'line_number' => (string) $config['line_number'],
                    'number_format' => (string) ($config['number_format'] ?? 'english'),
                ])
                ->throw();
        } catch (RequestException $exception) {
            Log::warning('FarazSMS pattern request failed.', [
                'mobile' => $mobile,
                'pattern_code' => $patternCode,
                'status' => $exception->response?->status(),
                'body' => $exception->response?->body(),
            ]);

            throw $exception;
        } catch (Throwable $exception) {
            Log::warning('FarazSMS pattern request could not be sent.', [
                'mobile' => $mobile,
                'pattern_code' => $patternCode,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
