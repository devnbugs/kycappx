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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'paystack' => [
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'preferred_bank' => env('PAYSTACK_DVA_BANK', env('APP_ENV', 'production') === 'production' ? 'wema-bank' : 'test-bank'),
    ],

    'kora' => [
        'base_url' => env('KORA_BASE_URL', 'https://api.korapay.com'),
        'secret_key' => env('KORA_SECRET_KEY'),
        'public_key' => env('KORA_PUBLIC_KEY'),
        'redirect_url' => env('KORA_REDIRECT_URL'),
        'bank_code' => env('KORA_DVA_BANK_CODE', env('APP_ENV', 'production') === 'production' ? '035' : '000'),
    ],
    'prembly' => [
        'base_url' => env('PREMBLY_BASE_URL', 'https://api.prembly.com'),
        'app_id' => env('PREMBLY_APP_ID'),
        'secret_key' => env('PREMBLY_SECRET_KEY'),
    ],

    'youverify' => [
        'base_url' => env('YOUVERIFY_BASE_URL', 'https://api.youverify.co'),
        'token' => env('YOUVERIFY_TOKEN'),
    ],

];
