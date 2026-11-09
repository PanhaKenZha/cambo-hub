<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 40)->unique();
            $table->string('eyebrow', 80)->nullable();
            $table->string('heading', 160);
            $table->string('subheading', 255)->nullable();
            $table->string('link_label', 60)->nullable();
            $table->string('link_url')->nullable();
            $table->unsignedTinyInteger('item_limit')->default(6);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
