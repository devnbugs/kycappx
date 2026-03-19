<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dedicated_virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_reference')->nullable();
            $table->string('customer_reference')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->string('status')->default('pending');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('requery_after')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
            $table->index(['provider', 'account_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dedicated_virtual_accounts');
    }
};
