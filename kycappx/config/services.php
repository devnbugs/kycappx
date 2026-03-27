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
        'products' => [
            'customers' => [
                'label' => 'Customer Profiles',
                'description' => 'Create and fetch customer records before downstream wallet actions.',
                'required' => true,
            ],
            'dedicated_accounts' => [
                'label' => 'Dedicated Virtual Accounts',
                'description' => 'Assign Paystack dedicated accounts for wallet topups and reconciliation.',
                'required' => true,
            ],
            'requery' => [
                'label' => 'Dedicated Account Requery',
                'description' => 'Requery Paystack dedicated accounts for late or missed transfer events.',
                'required' => true,
            ],
            'transactions' => [
                'label' => 'Transaction Lookup',
                'description' => 'Inspect transaction history and confirm payment states.',
                'required' => false,
            ],
            'transfer_recipients' => [
                'label' => 'Transfer Recipients',
                'description' => 'Prepare payout beneficiaries for future transfer flows.',
                'required' => false,
            ],
            'transfers' => [
                'label' => 'Transfers',
                'description' => 'Support outbound bank transfers when payouts are enabled later.',
                'required' => false,
            ],
            'refunds' => [
                'label' => 'Refunds',
                'description' => 'Enable refund operations for wallet or payment reversal workflows.',
                'required' => false,
            ],
            'identity' => [
                'label' => 'Identity Resolve APIs',
                'description' => 'Resolve account details and validate customers with Paystack identity APIs.',
                'required' => false,
            ],
        ],
    ],

    'kora' => [
        'base_url' => env('KORA_BASE_URL', 'https://api.korapay.com'),
        'secret_key' => env('KORA_SECRET_KEY'),
        'public_key' => env('KORA_PUBLIC_KEY'),
        'redirect_url' => env('KORA_REDIRECT_URL'),
        'bank_code' => env('KORA_DVA_BANK_CODE', env('APP_ENV', 'production') === 'production' ? '035' : '000'),
        'products' => [
            'checkout' => [
                'label' => 'Checkout Redirect',
                'description' => 'Initialize hosted checkout sessions for wallet funding.',
                'required' => true,
            ],
            'bank_transfer' => [
                'label' => 'Bank Transfer Charges',
                'description' => 'Charge via Kora bank transfer rails when needed.',
                'required' => false,
            ],
            'virtual_accounts' => [
                'label' => 'Virtual Accounts',
                'description' => 'Create and inspect Kora virtual bank accounts for wallet topups.',
                'required' => true,
            ],
            'account_holders' => [
                'label' => 'Virtual Account Holders',
                'description' => 'Manage account-holder KYC updates for advanced Kora account flows.',
                'required' => false,
            ],
            'verification' => [
                'label' => 'Identity Verification',
                'description' => 'Use Kora verification products like BVN, NIN, phone, and CAC where needed.',
                'required' => true,
            ],
            'virtual_cards' => [
                'label' => 'Virtual Cards',
                'description' => 'Create, fund, suspend, and inspect Kora-issued virtual cards.',
                'required' => false,
            ],
            'refunds' => [
                'label' => 'Refunds',
                'description' => 'Initiate and query refunds for Kora pay-ins.',
                'required' => false,
            ],
            'payouts' => [
                'label' => 'Payouts',
                'description' => 'Single and bulk disbursement capabilities for future payout workflows.',
                'required' => false,
            ],
            'balances' => [
                'label' => 'Balances',
                'description' => 'Read wallet balances and balance history from Kora.',
                'required' => false,
            ],
            'conversions' => [
                'label' => 'Currency Conversions',
                'description' => 'Quote and complete exchange-rate conversions where supported.',
                'required' => false,
            ],
        ],
    ],

    'interswitch' => [
        'base_url' => env('INTERSWITCH_BASE_URL', 'https://qa.interswitchng.com'),
        'client_id' => env('INTERSWITCH_CLIENT_ID'),
        'client_secret' => env('INTERSWITCH_CLIENT_SECRET'),
        'token_url' => env('INTERSWITCH_TOKEN_URL', 'https://qa.interswitchng.com/passport/oauth/token'),
        'products' => [
            'identity' => [
                'label' => 'Identity Marketplace',
                'description' => 'Run API Marketplace identity checks such as VIN or vehicle registry lookups.',
                'required' => true,
            ],
        ],
    ],

    'prembly' => [
        'base_url' => env('PREMBLY_BASE_URL', 'https://api.prembly.com'),
        'app_id' => env('PREMBLY_APP_ID'),
        'secret_key' => env('PREMBLY_SECRET_KEY'), 
        'countries' => ['NG'],
        'products' => require __DIR__.'/prembly_products.php',
    ],

    'squad' => [
        'base_url' => env('SQUAD_BASE_URL', env('APP_ENV', 'production') === 'production' ? 'https://api-d.squadco.com' : 'https://sandbox-api-d.squadco.com'),
        'secret_key' => env('SQUAD_SECRET_KEY'),
        'public_key' => env('SQUAD_PUBLIC_KEY'),
        'beneficiary_account' => env('SQUAD_BENEFICIARY_ACCOUNT'),
        'bank_name' => env('SQUAD_BANK_NAME', 'GTBank'),
        'sms_sender_id' => env('SQUAD_SMS_SENDER_ID', 'S-Alert'),
        'products' => [
            'virtual_accounts' => [
                'label' => 'Virtual Accounts',
                'description' => 'Create BVN-backed NGN virtual accounts for customer wallet funding.',
                'required' => true,
            ],
            'webhooks' => [
                'label' => 'Webhook Validation',
                'description' => 'Validate Squad transfer notifications and credit wallets automatically.',
                'required' => true,
            ],
            'sms_messages' => [
                'label' => 'Instant SMS',
                'description' => 'Send single or bulk SMS messages with a sender ID from the customer workspace.',
                'required' => true,
            ],
            'sms_templates' => [
                'label' => 'SMS Templates',
                'description' => 'Create reusable SMS templates inside Squad for onboarding and status notifications.',
                'required' => false,
            ],
        ],
    ],

    'cloudflare' => [
        'turnstile' => [
            'site_key' => env('TURNSTILE_SITE_KEY'),
            'secret_key' => env('TURNSTILE_SECRET_KEY'),
            'siteverify_url' => env('TURNSTILE_SITEVERIFY_URL', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
            'expected_host' => env('TURNSTILE_EXPECTED_HOST'),
        ],
    ],

];
