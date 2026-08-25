<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The files an applicant uploads: ID or passport copy, KCSE result slip,
 * passport photo, and any number of optional certificates.
 *
 * A row per file rather than a JSON column on `applications`, because the
 * optional certificates are many, each needs its own original filename and
 * size, and a file the admin deletes has to leave without rewriting a blob.
 *
 * `path` points at the PRIVATE disk. These are national IDs: nothing here is
 * ever served from public storage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();

            // id_copy | result_slip | passport_photo | certificate
            $table->string('kind', 30)->index();

            $table->string('original_name');
            $table->string('path');
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->timestamps();

            // The three required documents are single: re-uploading replaces
            // rather than accumulating. Certificates are many, so they are not
            // constrained here -- the controller enforces the difference.
            $table->index(['application_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
    }
};
