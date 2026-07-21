<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'farazsms' => [
        'api_key' => env('FARAZSMS_API_KEY'),
        'base_url' => env('FARAZSMS_BASE_URL', 'https://api.iranpayamak.com'),
        'line_number' => env('FARAZSMS_LINE_NUMBER'),
        'otp_pattern_code' => env('FARAZSMS_OTP_PATTERN_CODE'),
        'otp_attribute' => env('FARAZSMS_OTP_ATTRIBUTE', 'code'),
        'warranty_activation' => [
            'enabled' => filter_var(env('WARRANTY_ACTIVATION_SMS_ENABLED', false), FILTER_VALIDATE_BOOL),
            'pattern_code' => env('FARAZSMS_WARRANTY_ACTIVATION_PATTERN_CODE'),
            'product_title_attribute' => env('FARAZSMS_WARRANTY_ACTIVATION_PRODUCT_TITLE_ATTRIBUTE', 'ptitle'),
            'product_serial_attribute' => env('FARAZSMS_WARRANTY_ACTIVATION_PRODUCT_SERIAL_ATTRIBUTE', 'pserial'),
            'expires_at_attribute' => env('FARAZSMS_WARRANTY_ACTIVATION_EXPIRES_AT_ATTRIBUTE', 'wdate'),
        ],
        'number_format' => env('FARAZSMS_NUMBER_FORMAT', 'english'),
        'timeout' => (int) env('FARAZSMS_TIMEOUT_SECONDS', 10),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
