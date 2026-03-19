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
        Schema::create('verification_services', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique(); // BVN, NIN, CAC, BANK_RESOLVE
            $table->string('name');
            $table->string('type'); // kyc, kyb, bank, aml, doc
            $table->string('country', 2)->default('NG'); // ISO alpha-2

            $table->boolean('is_active')->default(true);

            $table->decimal('default_price', 18, 2)->default(0); // your selling price
            $table->decimal('default_cost', 18, 2)->default(0);  // your provider cost estimate

            $table->json('required_fields')->nullable(); // for UI + validation hints
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_services');
    }
};
