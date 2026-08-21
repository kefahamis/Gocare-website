<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('description')->nullable();
            $table->text('highlight')->nullable();
            $table->json('points')->nullable();
            $table->string('image');
            $table->string('alt_text')->nullable();
            $table->string('primary_label')->nullable();
            $table->string('primary_url')->nullable();
            $table->string('primary_style')->default('primary');
            $table->string('secondary_label')->nullable();
            $table->string('secondary_url')->nullable();
            $table->string('secondary_style')->default('white');
            $table->boolean('open_new_tab')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
