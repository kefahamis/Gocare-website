<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SMTP credentials editable from the admin panel.
 *
 * A single row. While `enabled` is false the site keeps using whatever is in
 * .env, so saving half-finished settings cannot silently break outgoing mail --
 * and a bad save can always be switched back off without a deploy.
 *
 * `password` is encrypted by the model cast, so the plaintext never sits in the
 * database, in a query log, or in a database backup.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('host')->nullable();
            $table->unsignedSmallInteger('port')->default(587);
            $table->string('encryption', 10)->nullable();   // tls | ssl | null
            $table->string('username')->nullable();
            $table->text('password')->nullable();           // encrypted
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->string('last_tested_to')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->text('last_test_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
