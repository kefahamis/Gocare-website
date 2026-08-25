<?php

//
// One row per confirmation Safaricom posts to the Confirmation URL.
// trans_id is the M-Pesa receipt (e.g. SHK3XY8ZT9), the same code the customer
// sees in their SMS and types into the "Pay manually" form. The unique index is
// what makes a code single-use across applications.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_c2b_payments', function (Blueprint $table) {
            $table->id();
            $table->string('trans_id', 20)->unique();             // TransID  - M-Pesa receipt number
            $table->string('transaction_type', 30)->nullable();   // "Buy Goods" or "Pay Bill"
            $table->dateTime('trans_time')->nullable();           // TransTime parsed from YYYYMMDDHHmmss
            $table->decimal('amount', 12, 2);                     // TransAmount
            $table->string('business_short_code', 10)->nullable();// which shortcode Safaricom reports (till or store)
            $table->string('bill_ref_number', 30)->nullable();    // empty for Buy Goods
            $table->string('msisdn', 30)->nullable();             // masked by Safaricom on C2B v2
            $table->string('first_name', 60)->nullable();
            $table->string('middle_name', 60)->nullable();
            $table->string('last_name', 60)->nullable();
            $table->json('payload');                              // the raw confirmation body, for audits
            $table->unsignedBigInteger('application_id')->nullable()->index(); // the application this payment settles
            $table->timestamp('claimed_at')->nullable();          // set when a customer's code entry wins this row
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_c2b_payments');
    }
};
