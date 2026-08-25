<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contact form behaviour, editable without a deploy: who is notified, what the
 * notification says, and whether the sender gets an acknowledgement.
 *
 * A single row. Every field falls back to its previous hard-coded value when
 * left blank, so an empty settings row behaves exactly as the site did before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('enabled')->default(true);
            $table->json('recipients')->nullable();     // who receives the message
            $table->json('cc')->nullable();
            $table->string('subject')->nullable();      // subject of the notification
            $table->string('success_message')->nullable();

            // Acknowledgement to whoever filled the form. Off by default: it
            // mails an address supplied by an anonymous visitor, so it can be
            // pointed at a third party by anyone who wants to.
            $table->boolean('auto_reply')->default(false);
            $table->string('auto_reply_subject')->nullable();
            $table->text('auto_reply_body')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};
