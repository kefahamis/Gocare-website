<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tunables for the contact form's spam defences.
 *
 * The honeypot itself has nothing to configure -- it is either tripped or it is
 * not -- but the time trap and the rate limit both trade false positives
 * against protection, so they need to be adjustable without a deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_settings', function (Blueprint $table) {
            // Seconds a genuine visitor needs to read and fill the form. A bot
            // posts in well under one.
            $table->unsignedSmallInteger('spam_min_seconds')->default(3);

            // Submissions allowed per IP per hour. 0 disables the limit.
            $table->unsignedSmallInteger('spam_max_per_hour')->default(5);
        });
    }

    public function down(): void
    {
        Schema::table('contact_settings', function (Blueprint $table) {
            $table->dropColumn(['spam_min_seconds', 'spam_max_per_hour']);
        });
    }
};
