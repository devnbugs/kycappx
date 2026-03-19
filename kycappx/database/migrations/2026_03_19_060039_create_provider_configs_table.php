<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provider_configs', function (Blueprint $table) {
            $table->id();

            $table->string('provider')->unique(); // prembly, paystack, kora
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(1); // 1 = first, 2 = fallback

            $table->json('config')->nullable(); // store keys? no, store refs to env names or non-secret settings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_configs');
    }
};
