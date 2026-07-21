<?php

namespace App\Contracts;

interface SmsProvider
{
    public function sendOtp(string $mobile, string $code): void;

    /**
     * @param  array<string, string>  $attributes
     */
    public function sendPattern(string $mobile, string $patternCode, array $attributes): void;
}
