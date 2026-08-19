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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->string('protocol')->default('https');
            $table->string('favicon_url')->nullable();
            $table->float('domain_rank')->default(1.0);
            $table->integer('total_pages')->default(0);
            $table->string('crawl_status')->default('idle'); // idle, crawling, paused, blocked
            $table->timestamp('last_crawled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('web_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->string('url', 2048)->unique();
            $table->string('domain')->index();
            $table->string('title')->nullable()->index();
            $table->text('description')->nullable();
            $table->json('keywords')->nullable();
            $table->json('headings')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('category')->default('all')->index(); // all, tech, news, code, etc.
            $table->string('language', 10)->default('en')->index();
            $table->string('content_hash', 64)->nullable()->index();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('og_image', 2048)->nullable();
            $table->float('page_rank')->default(1.0)->index();
            $table->integer('http_status')->default(200);
            $table->float('response_time_ms')->default(0);
            $table->integer('in_links_count')->default(0);
            $table->integer('out_links_count')->default(0);
            $table->boolean('is_indexed')->default(true)->index();
            $table->timestamp('crawled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('crawl_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('seed_url', 2048);
            $table->string('status')->default('queued')->index(); // queued, running, completed, failed, paused
            $table->integer('max_depth')->default(3);
            $table->integer('max_pages')->default(200);
            $table->integer('concurrency')->default(5);
            $table->integer('pages_crawled')->default(0);
            $table->integer('pages_discovered')->default(0);
            $table->integer('pages_indexed')->default(0);
            $table->integer('errors_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query')->index();
            $table->string('category')->default('all');
            $table->integer('results_count')->default(0);
            $table->float('execution_time_ms')->default(0);
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_queries');
        Schema::dropIfExists('crawl_jobs');
        Schema::dropIfExists('web_pages');
        Schema::dropIfExists('domains');
    }
};
