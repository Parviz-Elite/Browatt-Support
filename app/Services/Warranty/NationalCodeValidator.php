<?php

namespace App\Services\Warranty;

class NationalCodeValidator
{
    public function isValid(string $code): bool
    {
        $code = preg_replace('/\D+/', '', $code) ?? '';

        if (! preg_match('/^\d{10}$/', $code)) {
            return false;
        }

        if (preg_match('/^(\d)\1{9}$/', $code)) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $code[$i] * (10 - $i);
        }

        $remainder = $sum % 11;
        $checkDigit = (int) $code[9];

        return $remainder < 2
            ? $checkDigit === $remainder
            : $checkDigit === 11 - $remainder;
    }
}
