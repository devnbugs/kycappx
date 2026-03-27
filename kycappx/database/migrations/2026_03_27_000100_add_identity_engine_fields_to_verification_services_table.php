<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_services', function (Blueprint $table) {
            $table->json('engine_preferences')->nullable()->after('required_fields');
            $table->string('response_template')->default('auto')->after('engine_preferences');
        });

        DB::table('verification_services')
            ->whereNull('engine_preferences')
            ->update([
                'engine_preferences' => json_encode(['prembly']),
                'response_template' => 'auto',
            ]);

        DB::table('verification_services')
            ->whereIn('code', ['NIN_BASIC', 'NIN_ADVANCE', 'NIN_WITH_FACE', 'NIN_LEVEL_2'])
            ->update([
                'engine_preferences' => json_encode(['prembly', 'kora']),
                'response_template' => 'ninSlip',
            ]);

        DB::table('verification_services')
            ->whereIn('code', ['BVN_ADVANCE', 'BVN_BASIC', 'BVN_FACE_VALIDATION'])
            ->update([
                'engine_preferences' => json_encode(['prembly', 'kora']),
                'response_template' => 'bvnSlip',
            ]);

        DB::table('verification_services')
            ->where('code', 'GET_BVN_WITH_PHONE_NUMBER')
            ->update([
                'engine_preferences' => json_encode(['prembly']),
                'response_template' => 'bvnSlip',
            ]);

        DB::table('verification_services')
            ->where('code', 'PLATE_NUMBER_VERIFICATION')
            ->update([
                'response_template' => 'vehicleSlip',
            ]);

        DB::table('verification_services')->updateOrInsert(
            ['code' => 'NIN_WITH_PHONE'],
            [
                'name' => 'NIN With Phone',
                'type' => 'kyc',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 240,
                'default_cost' => 160,
                'required_fields' => json_encode(['phone', 'first_name', 'last_name', 'dob']),
                'engine_preferences' => json_encode(['kora']),
                'response_template' => 'ninSlip',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        DB::table('verification_services')->updateOrInsert(
            ['code' => 'VIN_LOOKUP'],
            [
                'name' => 'VIN Lookup',
                'type' => 'vehicle',
                'country' => 'NG',
                'is_active' => true,
                'default_price' => 200,
                'default_cost' => 120,
                'required_fields' => json_encode(['vin']),
                'engine_preferences' => json_encode(['interswitch']),
                'response_template' => 'vehicleSlip',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        if (Schema::hasTable('provider_configs')) {
            $providers = [
                'prembly' => [
                    'is_active' => false,
                    'priority' => 1,
                    'config' => [
                        'channel' => 'identity',
                        'mode' => 'live',
                        'timeout_seconds' => 30,
                        'country_scope' => ['NG'],
                        'publicLabel' => 'v1',
                    ],
                ],
                'kora' => [
                    'is_active' => false,
                    'priority' => 3,
                    'config' => [
                        'channel' => 'identity',
                        'mode' => 'live',
                        'timeout_seconds' => 30,
                        'publicLabel' => 'v2',
                    ],
                ],
                'interswitch' => [
                    'is_active' => false,
                    'priority' => 4,
                    'config' => [
                        'channel' => 'identity',
                        'mode' => 'sandbox',
                        'timeout_seconds' => 30,
                        'publicLabel' => 'v3',
                        'enabled_products' => [
                            'identity' => true,
                        ],
                        'verificationRoutes' => [
                            'VIN_LOOKUP' => [
                                'productKey' => 'vinLookup',
                                'endpointPath' => env('INTERSWITCH_VIN_PATH'),
                                'requestMethod' => env('INTERSWITCH_VIN_METHOD', 'POST'),
                            ],
                        ],
                    ],
                ],
            ];

            foreach ($providers as $provider => $attributes) {
                $existing = DB::table('provider_configs')->where('provider', $provider)->first();

                if ($existing) {
                    $existingConfig = json_decode((string) ($existing->config ?? '{}'), true);

                    DB::table('provider_configs')
                        ->where('provider', $provider)
                        ->update([
                            'config' => json_encode(array_replace_recursive($attributes['config'], is_array($existingConfig) ? $existingConfig : [])),
                            'updated_at' => now(),
                        ]);

                    continue;
                }

                DB::table('provider_configs')->insert([
                    'provider' => $provider,
                    'is_active' => $attributes['is_active'],
                    'priority' => $attributes['priority'],
                    'config' => json_encode($attributes['config']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('provider_configs')) {
            DB::table('provider_configs')
                ->where('provider', 'interswitch')
                ->delete();
        }

        DB::table('verification_services')
            ->whereIn('code', ['NIN_WITH_PHONE', 'VIN_LOOKUP'])
            ->delete();

        Schema::table('verification_services', function (Blueprint $table) {
            $table->dropColumn(['engine_preferences', 'response_template']);
        });
    }
};
