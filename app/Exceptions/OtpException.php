<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class OtpException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $reason = 'otp_error',
        public readonly ?int $retryAfter = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function invalidMobile(): self
    {
        return new self('Invalid mobile number.', 'invalid_mobile');
    }

    public static function resendTooSoon(int $seconds): self
    {
        return new self("OTP resend is allowed in {$seconds} seconds.", 'resend_too_soon', $seconds);
    }

    public static function tooManyRequests(): self
    {
        return new self('Too many OTP requests.', 'too_many_requests');
    }

    public static function invalidCode(): self
    {
        return new self('Invalid OTP code.', 'invalid_code');
    }

    public static function maxAttemptsReached(): self
    {
        return new self('Maximum OTP attempts reached.', 'max_attempts_reached');
    }

    public static function smsProviderNotConfigured(string $provider): self
    {
        return new self("SMS provider [{$provider}] is not configured.", 'sms_provider_not_configured');
    }
}
