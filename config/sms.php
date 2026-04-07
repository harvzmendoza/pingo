<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default SMS driver
    |--------------------------------------------------------------------------
    |
    | Supported: "log", "unisms"
    | "log" writes to the application log and succeeds (local / tests).
    | "unisms" uses App\Services\Sms\Providers\UniSmsProvider when implemented.
    |
    */

    'driver' => env('SMS_DRIVER', 'log'),

    'unisms' => [
        'api_url' => env('UNISMS_API_URL', 'https://unismsapi.com/api/sms'),
        'api_key' => env('UNISMS_API_KEY'),
        'sender_id' => env('UNISMS_SENDER_ID'),
    ],

];
