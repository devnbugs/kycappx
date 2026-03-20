<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('squad_dva_enabled')->default(true)->after('kora_dva_enabled');
            $table->boolean('sms_enabled')->default(true)->after('squad_dva_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'squad_dva_enabled',
                'sms_enabled',
            ]);
        });
    }
};
