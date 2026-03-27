<?php

return [
    'providers' => [
        'prembly' => [
            'adminLabel' => 'Prembly',
            'publicLabel' => 'v1',
            'productToggle' => null,
        ],
        'kora' => [
            'adminLabel' => 'Kora Identity',
            'publicLabel' => 'v2',
            'productToggle' => 'verification',
        ],
        'interswitch' => [
            'adminLabel' => 'Interswitch API Marketplace',
            'publicLabel' => 'v3',
            'productToggle' => 'identity',
        ],
    ],

    'serviceDefaults' => [
        'NIN_BASIC' => [
            'enginePreferences' => ['prembly', 'kora'],
            'responseTemplate' => 'ninSlip',
            'routeOverrides' => [
                'kora' => ['productKey' => 'ninLookup'],
            ],
        ],
        'NIN_ADVANCE' => [
            'enginePreferences' => ['prembly', 'kora'],
            'responseTemplate' => 'ninSlip',
            'routeOverrides' => [
                'kora' => ['productKey' => 'ninLookup'],
            ],
        ],
        'NIN_WITH_FACE' => [
            'enginePreferences' => ['prembly', 'kora'],
            'responseTemplate' => 'ninSlip',
            'routeOverrides' => [
                'kora' => ['productKey' => 'ninLookup'],
            ],
        ],
        'NIN_LEVEL_2' => [
            'enginePreferences' => ['prembly', 'kora'],
            'responseTemplate' => 'ninSlip',
            'routeOverrides' => [
                'kora' => ['productKey' => 'ninLookup'],
            ],
        ],
        'BVN_ADVANCE' => [
            'enginePreferences' => ['prembly', 'kora'],
            'responseTemplate' => 'bvnSlip',
            'routeOverrides' => [
                'kora' => ['productKey' => 'bvnLookup'],
            ],
        ],
        'BVN_BASIC' => [
            'enginePreferences' => ['prembly', 'kora'],
            'responseTemplate' => 'bvnSlip',
            'routeOverrides' => [
                'kora' => ['productKey' => 'bvnLookup'],
            ],
        ],
        'BASIC_PHONE_NUMBER' => [
            'enginePreferences' => ['prembly', 'kora'],
            'routeOverrides' => [
                'kora' => ['productKey' => 'phoneLookup'],
            ],
        ],
        'ADVANCE_CAC' => [
            'enginePreferences' => ['prembly', 'kora'],
            'routeOverrides' => [
                'kora' => ['productKey' => 'cacLookup'],
            ],
        ],
        'GET_BVN_WITH_PHONE_NUMBER' => [
            'enginePreferences' => ['prembly'],
            'responseTemplate' => 'bvnSlip',
        ],
        'PLATE_NUMBER_VERIFICATION' => [
            'enginePreferences' => ['prembly'],
            'responseTemplate' => 'vehicleSlip',
        ],
        'NIN_WITH_PHONE' => [
            'enginePreferences' => ['kora'],
            'responseTemplate' => 'ninSlip',
            'routeOverrides' => [
                'kora' => ['productKey' => 'advancedPhoneSearch'],
            ],
        ],
        'VIN_LOOKUP' => [
            'enginePreferences' => ['interswitch'],
            'responseTemplate' => 'vehicleSlip',
            'routeOverrides' => [
                'interswitch' => ['productKey' => 'vinLookup'],
            ],
        ],
    ],

    'serviceCatalog' => [
        'NIN_WITH_PHONE' => [
            'label' => 'NIN With Phone',
            'normalizer' => 'nin',
            'requestBody' => [
                ['name' => 'phone', 'type' => 'string', 'required' => true, 'description' => 'Phone number linked to the NIN record.'],
                ['name' => 'first_name', 'type' => 'string', 'required' => false, 'description' => 'Optional data-match first name.'],
                ['name' => 'last_name', 'type' => 'string', 'required' => false, 'description' => 'Optional data-match last name.'],
                ['name' => 'dob', 'type' => 'string', 'required' => false, 'description' => 'Optional data-match date of birth in YYYY-MM-DD format.'],
            ],
            'service' => [
                'code' => 'NIN_WITH_PHONE',
                'name' => 'NIN With Phone',
                'type' => 'kyc',
                'country' => 'NG',
                'defaultPrice' => 240,
                'defaultCost' => 160,
                'isActive' => true,
                'featured' => true,
                'enginePreferences' => ['kora'],
                'responseTemplate' => 'ninSlip',
            ],
        ],
        'VIN_LOOKUP' => [
            'label' => 'VIN Lookup',
            'normalizer' => 'vehicle',
            'requestBody' => [
                ['name' => 'vin', 'type' => 'string', 'required' => true, 'description' => 'Vehicle identification number (VIN).'],
            ],
            'service' => [
                'code' => 'VIN_LOOKUP',
                'name' => 'VIN Lookup',
                'type' => 'vehicle',
                'country' => 'NG',
                'defaultPrice' => 200,
                'defaultCost' => 120,
                'isActive' => true,
                'featured' => true,
                'enginePreferences' => ['interswitch'],
                'responseTemplate' => 'vehicleSlip',
            ],
        ],
    ],

    'routeCatalog' => [
        'kora' => [
            'bvnLookup' => [
                'endpointPath' => '/merchant/api/v1/identities/ng/bvn',
                'requestMethod' => 'POST',
                'normalizer' => 'bvn',
                'productToggle' => 'verification',
            ],
            'ninLookup' => [
                'endpointPath' => '/merchant/api/v1/identities/ng/nin',
                'requestMethod' => 'POST',
                'normalizer' => 'nin',
                'productToggle' => 'verification',
            ],
            'phoneLookup' => [
                'endpointPath' => '/merchant/api/v1/identities/ng/phone',
                'requestMethod' => 'POST',
                'normalizer' => 'phone',
                'productToggle' => 'verification',
            ],
            'advancedPhoneSearch' => [
                'endpointPath' => '/merchant/api/v1/identities/ng/nin-phone',
                'requestMethod' => 'POST',
                'normalizer' => 'nin',
                'productToggle' => 'verification',
            ],
            'cacLookup' => [
                'endpointPath' => '/merchant/api/v1/identities/ng/cac',
                'requestMethod' => 'POST',
                'normalizer' => 'cac',
                'productToggle' => 'verification',
            ],
        ],
        'interswitch' => [
            'vinLookup' => [
                'endpointPath' => env('INTERSWITCH_VIN_PATH'),
                'requestMethod' => env('INTERSWITCH_VIN_METHOD', 'POST'),
                'normalizer' => 'vehicle',
                'productToggle' => 'identity',
                'payloadKey' => env('INTERSWITCH_VIN_PAYLOAD_KEY', 'vin'),
                'successField' => env('INTERSWITCH_VIN_SUCCESS_FIELD', 'responseCode'),
                'successValue' => env('INTERSWITCH_VIN_SUCCESS_VALUE', '00'),
                'referencePath' => env('INTERSWITCH_VIN_REFERENCE_PATH', 'transactionReference'),
                'dataPath' => env('INTERSWITCH_VIN_DATA_PATH', 'data'),
            ],
        ],
    ],
];
