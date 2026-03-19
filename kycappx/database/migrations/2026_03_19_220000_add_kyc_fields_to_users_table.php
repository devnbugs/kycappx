<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kyc_level')->default('level_0')->after('preferred_funding_provider');
            $table->text('kyc_profile')->nullable()->after('kyc_level');
        });

        if (Schema::hasTable('provider_configs')) {
            DB::table('provider_configs')
                ->where('provider', 'youverify')
                ->delete();
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kyc_level', 'kyc_profile']);
        });
    }
};
