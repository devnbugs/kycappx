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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            $table->string('type'); // credit, debit, refund
            $table->decimal('amount', 18, 2);
            $table->string('currency', 3)->default('NGN');

            $table->string('reference')->unique();
            $table->string('status')->default('success'); // pending, success, failed
            $table->string('source')->nullable(); // paystack, kora, internal
            $table->string('description')->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
