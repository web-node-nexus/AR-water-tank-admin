<?php

return [
    'otp' => [
        'driver' => env('OTP_DRIVER', 'log'), // log, msg91, fast2sms
        'length' => 6,
        'expiry_minutes' => 10,
        'msg91' => [
            'auth_key' => env('MSG91_AUTH_KEY'),
            'template_id' => env('MSG91_OTP_TEMPLATE_ID'),
            'sender_id' => env('MSG91_SENDER_ID', 'ARWTC'),
        ],
    ],

    'exotel' => [
        'sid' => env('EXOTEL_SID'),
        'api_key' => env('EXOTEL_API_KEY'),
        'api_token' => env('EXOTEL_API_TOKEN'),
        'virtual_number' => env('EXOTEL_VIRTUAL_NUMBER'), // Company virtual number shown to customer
        'subdomain' => env('EXOTEL_SUBDOMAIN', 'api.in'),
    ],

    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'log'), // log, gupshup, interakt
        'api_url' => env('WHATSAPP_API_URL'),
        'api_key' => env('WHATSAPP_API_KEY'),
        'source_number' => env('WHATSAPP_SOURCE_NUMBER'),
        'template_before_photo' => env('WHATSAPP_TEMPLATE_BEFORE', 'before_cleaning_photo'),
        'template_after_photo' => env('WHATSAPP_TEMPLATE_AFTER', 'after_cleaning_photo'),
        'template_job_complete' => env('WHATSAPP_TEMPLATE_COMPLETE', 'job_completed'),
    ],
];
