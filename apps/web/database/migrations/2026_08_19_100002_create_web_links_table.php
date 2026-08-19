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
        if (!Schema::hasTable('web_links')) {
            Schema::create('web_links', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('source_page_id')->nullable()->constrained('web_pages')->cascadeOnDelete();
                $table->string('source_url', 2048)->index();
                $table->string('source_domain')->index();
                $table->foreignUuid('target_page_id')->nullable()->constrained('web_pages')->nullOnDelete();
                $table->string('target_url', 2048)->index();
                $table->string('target_domain')->index();
                $table->string('anchor_text')->nullable();
                $table->boolean('is_external')->default(false)->index();
                $table->string('rel')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_links');
    }
};
