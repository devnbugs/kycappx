<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('squad');
            $table->string('action');
            $table->string('sender_id')->nullable();
            $table->string('reference')->unique();
            $table->string('remote_reference')->nullable();
            $table->string('status')->default('pending');
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->json('recipients')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'action', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_dispatches');
    }
};
