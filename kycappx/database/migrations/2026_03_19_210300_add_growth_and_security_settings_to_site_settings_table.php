<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('google_auth_enabled')->default(true)->after('dark_mode_enabled');
            $table->boolean('dva_enabled')->default(true)->after('google_auth_enabled');
            $table->boolean('paystack_dva_enabled')->default(true)->after('dva_enabled');
            $table->boolean('kora_dva_enabled')->default(true)->after('paystack_dva_enabled');
            $table->decimal('user_pro_discount_rate', 5, 2)->default(10)->after('kora_dva_enabled');
            $table->string('default_funding_provider')->default('paystack')->after('user_pro_discount_rate');
            $table->string('logo_text', 12)->default('KX')->after('default_funding_provider');
            $table->string('header_notice')->nullable()->after('logo_text');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'google_auth_enabled',
                'dva_enabled',
                'paystack_dva_enabled',
                'kora_dva_enabled',
                'user_pro_discount_rate',
                'default_funding_provider',
                'logo_text',
                'header_notice',
            ]);
        });
    }
};
