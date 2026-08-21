<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_token')->unique();
            $table->string('transaction_code')->index();
            $table->string('phone')->nullable();
            $table->string('application_reference')->nullable()->index();
            // Whoever first confirmed this code owns it, so it cannot be reused.
            $table->string('claim_key')->nullable()->index();
            $table->string('status')->default('pending')->index();

            $table->string('conversation_id')->nullable();
            $table->string('originator_conversation_id')->nullable()->unique();

            $table->string('result_code')->nullable();
            $table->text('result_description')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('receipt_party')->nullable();
            $table->string('transaction_status')->nullable();
            $table->timestamp('transaction_completed_at')->nullable();

            $table->json('request_payload')->nullable();
            $table->json('result_payload')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_verifications');
    }
};
