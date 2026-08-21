<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fields the idempotent payment paths need.
 *
 * These arrived after 2026_08_21_000001 had already been applied on some
 * databases, so they get their own migration rather than an edit to that one.
 * Every column is guarded: a database that picked them up from an earlier
 * revision of that migration is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Every CheckoutRequestID this application has pushed. Retrying the
            // prompt reuses the row, so a callback for an earlier attempt must
            // still find it instead of being dropped as unmatched.
            if (! Schema::hasColumn('applications', 'checkout_request_ids')) {
                $table->json('checkout_request_ids')->nullable()->after('checkout_request_id');
            }

            // When a transaction status query was sent, so a repeated submission
            // of the same code waits for the in-flight result instead of asking
            // Safaricom again.
            if (! Schema::hasColumn('applications', 'status_queried_at')) {
                $table->timestamp('status_queried_at')->nullable()->after('status_conversation_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            foreach (['checkout_request_ids', 'status_queried_at'] as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
