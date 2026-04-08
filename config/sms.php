<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS driver
    |--------------------------------------------------------------------------
    |
    | Supported: "log", "unisms", "skysms"
    | "log" writes to the application log and succeeds (local / tests).
    | "unisms" uses App\Services\Sms\Providers\UniSmsProvider.
    | "skysms" uses App\Services\Sms\Providers\SkySmsProvider (https://skysms.skyio.site/docs).
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    'unisms' => [
        'api_url' => env('UNISMS_API_URL', 'https://unismsapi.com/api/sms'),
        'api_key' => env('UNISMS_API_KEY'),
        'sender_id' => env('UNISMS_SENDER_ID'),
    ],

    'skysms' => [
        'base_url' => env('SKYSMS_BASE_URL', 'https://skysms.skyio.site'),
        'api_key' => env('SKYSMS_API_KEY'),
        'use_subscription' => filter_var(env('SKYSMS_USE_SUBSCRIPTION', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
