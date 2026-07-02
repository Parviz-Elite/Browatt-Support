<?php

return [
    'enabled' => env('MEHRSOFT_ENABLED', false),

    'wsdl_url' => env('MEHRSOFT_WSDL_URL', 'https://www.mehrsofts.com/webservice/mehraccws.asmx?WSDL'),
    'username' => env('MEHRSOFT_USERNAME'),
    'password' => env('MEHRSOFT_PASSWORD'),
    'financial_unit' => (int) env('MEHRSOFT_FINANCIAL_UNIT_CODE', 0),

    'soap' => [
        'exceptions' => true,
        'trace' => env('MEHRSOFT_SOAP_TRACE', false),
        'cache_wsdl' => WSDL_CACHE_BOTH,
        'connection_timeout' => (int) env('MEHRSOFT_TIMEOUT_SECONDS', 20),
    ],

    'logging' => [
        'enabled' => env('MEHRSOFT_LOGGING_ENABLED', true),
        'response_preview_limit' => (int) env('MEHRSOFT_RESPONSE_PREVIEW_LIMIT', 2000),
    ],
];
