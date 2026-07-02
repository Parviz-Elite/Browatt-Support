<?php

namespace App\Services\Sms;

use App\Contracts\SmsProvider;
use App\Exceptions\OtpException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        try {
            Http::baseUrl(rtrim((string) $config['base_url'], '/'))
                ->withHeaders([
                    'Api-Key' => (string) $config['api_key'],
                ])
                ->acceptJson()
                ->asJson()
                ->timeout((int) ($config['timeout'] ?? 10))
                ->post('/ws/v1/sms/pattern', [
                    'code' => (string) $config['otp_pattern_code'],
                    'attributes' => [
                        (string) ($config['otp_attribute'] ?? 'code') => $code,
                    ],
                    'recipient' => $mobile,
                    'line_number' => (string) $config['line_number'],
                    'number_format' => (string) ($config['number_format'] ?? 'english'),
                ])
                ->throw();
        } catch (RequestException $exception) {
            Log::warning('FarazSMS OTP request failed.', [
                'mobile' => $mobile,
                'status' => $exception->response?->status(),
                'body' => $exception->response?->body(),
            ]);

            throw $exception;
        } catch (Throwable $exception) {
            Log::warning('FarazSMS OTP request could not be sent.', [
                'mobile' => $mobile,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
