<?php

namespace App\Exceptions;

use RuntimeException;

class OtpException extends RuntimeException
{
    public static function invalidMobile(): self
    {
        return new self('Invalid mobile number.');
    }

    public static function resendTooSoon(int $seconds): self
    {
        return new self("OTP resend is allowed in {$seconds} seconds.");
    }

    public static function tooManyRequests(): self
    {
        return new self('Too many OTP requests.');
    }

    public static function invalidCode(): self
    {
        return new self('Invalid OTP code.');
    }

    public static function maxAttemptsReached(): self
    {
        return new self('Maximum OTP attempts reached.');
    }

    public static function smsProviderNotConfigured(string $provider): self
    {
        return new self("SMS provider [{$provider}] is not configured.");
    }
}
