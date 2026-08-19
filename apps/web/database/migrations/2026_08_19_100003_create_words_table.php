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
        if (!Schema::hasTable('words')) {
            Schema::create('words', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('word')->unique()->index();
                $table->string('language', 10)->default('en')->index();
                $table->unsignedBigInteger('frequency')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
