<?php

namespace App\Contracts;

interface SmsProvider
{
    public function sendOtp(string $mobile, string $code): void;
}
