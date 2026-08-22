<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Its own migration rather than an edit to the create: that one may
        // already have run, and an edited migration never re-runs.
        Schema::table('page_seo', function (Blueprint $table) {
            $table->string('canonical_url')->nullable()->after('og_image');
        });
    }

    public function down(): void
    {
        Schema::table('page_seo', function (Blueprint $table) {
            $table->dropColumn('canonical_url');
        });
    }
};
