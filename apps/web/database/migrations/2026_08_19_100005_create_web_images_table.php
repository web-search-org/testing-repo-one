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
        if (!Schema::hasTable('web_images')) {
            Schema::create('web_images', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('web_page_id')->nullable()->constrained('web_pages')->cascadeOnDelete();
                $table->foreignUuid('domain_id')->nullable()->constrained('domains')->cascadeOnDelete();
                $table->string('domain')->index();
                $table->string('page_url', 2048)->index();
                $table->string('image_url', 2048)->index();
                $table->string('local_path')->nullable();
                $table->string('thumbnail_path')->nullable();
                $table->text('alt_text')->nullable();
                $table->string('title')->nullable();
                $table->unsignedInteger('width')->default(0);
                $table->unsignedInteger('height')->default(0);
                $table->string('mime_type', 100)->default('image/jpeg');
                $table->unsignedInteger('size_bytes')->default(0);
                $table->float('aspect_ratio')->default(1.0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_images');
    }
};
