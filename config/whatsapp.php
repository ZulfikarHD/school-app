<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration untuk WhatsApp notification menggunakan Fonnte API
    | Get your API key from: https://fonnte.com
    |
    */

    'fonnte_api_key' => env('FONNTE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */

    'default_country_code' => env('WHATSAPP_COUNTRY_CODE', '62'),

    'max_retry_attempts' => env('WHATSAPP_MAX_RETRY', 3),

    'retry_delay_minutes' => env('WHATSAPP_RETRY_DELAY', 5),
];
