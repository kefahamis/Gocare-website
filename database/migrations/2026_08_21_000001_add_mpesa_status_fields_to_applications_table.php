<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fields for confirming a manually typed M-Pesa code with Safaricom.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // OriginatorConversationID of the Transaction Status query, so the
            // asynchronous result callback can be matched back to this row.
            // Every CheckoutRequestID this application has pushed. Retrying the
            // prompt reuses the row, so a callback for an earlier attempt must
            // still find it instead of being dropped as unmatched.
            $table->json('checkout_request_ids')->nullable()->after('checkout_request_id');

            $table->string('status_conversation_id')->nullable()->after('mpesa_receipt')->index();

            // When the query was sent, so a repeated submission of the same code
            // waits for the in-flight result instead of asking Safaricom again.
            $table->timestamp('status_queried_at')->nullable()->after('status_conversation_id');

            // Why a typed code could not be confirmed, shown back to the applicant.
            $table->string('payment_note')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['status_conversation_id']);
            $table->dropColumn(['checkout_request_ids', 'status_conversation_id', 'status_queried_at', 'payment_note']);
        });
    }
};
