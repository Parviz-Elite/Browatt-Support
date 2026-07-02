<?php

return [
    'sms_provider' => env('OTP_SMS_PROVIDER', 'farazsms'),

    'code_length' => (int) env('OTP_CODE_LENGTH', 6),
    'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 2),
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'resend_seconds' => (int) env('OTP_RESEND_SECONDS', 60),

    'rate_limits' => [
        'mobile' => [
            'max_attempts' => (int) env('OTP_MOBILE_MAX_REQUESTS', 5),
            'decay_seconds' => (int) env('OTP_MOBILE_DECAY_SECONDS', 3600),
        ],
        'ip' => [
            'max_attempts' => (int) env('OTP_IP_MAX_REQUESTS', 30),
            'decay_seconds' => (int) env('OTP_IP_DECAY_SECONDS', 3600),
        ],
    ],

    'verify_rate_limits' => [
        'mobile' => [
            'max_attempts' => (int) env('OTP_VERIFY_MOBILE_MAX_ATTEMPTS', 10),
            'decay_seconds' => (int) env('OTP_VERIFY_MOBILE_DECAY_SECONDS', 600),
        ],
        'ip' => [
            'max_attempts' => (int) env('OTP_VERIFY_IP_MAX_ATTEMPTS', 60),
            'decay_seconds' => (int) env('OTP_VERIFY_IP_DECAY_SECONDS', 3600),
        ],
    ],
];
