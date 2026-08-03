<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['courses', 'blog_posts', 'schools', 'site_pages'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->json('sections')->nullable()->after('content');
            });
        }

        Schema::table('sliders', function (Blueprint $table) {
            $table->string('accent_title')->nullable()->after('title');
            $table->string('title_suffix')->nullable()->after('accent_title');
        });
    }

    public function down(): void
    {
        foreach (['courses', 'blog_posts', 'schools', 'site_pages'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('sections');
            });
        }

        Schema::table('sliders', function (Blueprint $table) {
            $table->dropColumn(['accent_title', 'title_suffix']);
        });
    }
};
