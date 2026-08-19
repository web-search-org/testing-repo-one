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
            $table->uuid('id')->primary();
            $table->string('name')->unique()->index();
            $table->string('protocol')->default('https');
            $table->string('favicon_url')->nullable();
            $table->float('domain_rank')->default(1.0);
            $table->integer('total_pages')->default(0);
            $table->string('crawl_status')->default('idle'); // idle, crawling, paused, blocked
            $table->string('verification_token')->nullable()->index();
            $table->boolean('is_verified')->default(false)->index();
            $table->string('verification_method')->nullable(); // dns_txt, meta_tag, file_upload
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_crawled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('web_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('domain_id')->nullable()->constrained('domains')->nullOnDelete();
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
            $table->string('index_status')->default('indexed')->index(); // indexed, excluded_robots, not_found, server_error, duplicate
            $table->boolean('mobile_friendly')->default(true);
            $table->timestamp('last_inspected_at')->nullable();
            $table->timestamp('crawled_at')->nullable();
            $table->timestamps();
        });

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

        Schema::create('sitemaps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->string('url', 2048);
            $table->string('status')->default('submitted')->index(); // submitted, processing, success, error
            $table->integer('total_urls')->default(0);
            $table->integer('indexed_urls')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('last_crawled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('domain_performances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('domain_id')->constrained('domains')->cascadeOnDelete();
            $table->string('query')->index();
            $table->string('page_url', 2048)->nullable();
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->float('ctr')->default(0.0);
            $table->float('avg_position')->default(1.0);
            $table->date('recorded_date')->index();
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
            $table->uuid('id')->primary();
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
        Schema::dropIfExists('domain_performances');
        Schema::dropIfExists('sitemaps');
        Schema::dropIfExists('web_links');
        Schema::dropIfExists('web_pages');
        Schema::dropIfExists('domains');
    }
};
