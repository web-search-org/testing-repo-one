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
        if (!Schema::hasTable('favicons')) {
            Schema::create('favicons', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('domain')->unique()->index();
                $table->string('mime_type', 100)->default('image/x-icon');
                $table->longText('data_base64')->nullable();
                $table->string('original_url', 2048)->nullable();
                $table->unsignedInteger('size_bytes')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favicons');
    }
};
