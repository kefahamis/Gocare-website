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
        Schema::table('applications', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('status');
            $table->decimal('amount', 8, 2)->nullable()->after('phone');
            $table->string('checkout_request_id')->nullable()->after('amount');
            $table->string('mpesa_receipt')->nullable()->after('checkout_request_id');
            $table->string('payment_status')->default('pending')->after('mpesa_receipt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'amount',
                'checkout_request_id',
                'mpesa_receipt',
                'payment_status',
            ]);
        });
    }
};
