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
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verification_service_id')->constrained()->cascadeOnDelete();

            $table->string('reference')->unique();
            $table->string('status')->default('pending'); 
            // pending, processing, success, failed, manual_review, refunded

            $table->string('provider_used')->nullable();

            $table->decimal('customer_price', 18, 2)->default(0);
            $table->decimal('provider_cost', 18, 2)->default(0);

            $table->json('request_payload')->nullable();
            $table->json('normalized_response')->nullable();
            $table->json('raw_response')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_requests');
    }
};
