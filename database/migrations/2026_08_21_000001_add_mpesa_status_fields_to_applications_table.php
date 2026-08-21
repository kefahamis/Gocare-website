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
            $table->string('status_conversation_id')->nullable()->after('mpesa_receipt')->index();

            // Why a typed code could not be confirmed, shown back to the applicant.
            $table->string('payment_note')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['status_conversation_id']);
            $table->dropColumn(['status_conversation_id', 'payment_note']);
        });
    }
};
