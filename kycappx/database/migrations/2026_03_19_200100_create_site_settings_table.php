<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Kycappx');
            $table->string('site_tagline')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('default_currency', 3)->default('NGN');
            $table->string('default_theme')->default('system');
            $table->boolean('registration_enabled')->default(true);
            $table->boolean('wallet_funding_enabled')->default(true);
            $table->boolean('verification_enabled')->default(true);
            $table->boolean('dark_mode_enabled')->default(true);
            $table->text('maintenance_message')->nullable();
            $table->string('footer_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
