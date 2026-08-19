# 🌐 Web-Search.org

[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL_3.0-blue.svg)](LICENSE)
[![GitHub Org](https://img.shields.io/badge/GitHub-web--search--org-181717.svg?logo=github&logoColor=white)](https://github.com/web-search-org)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com)
[![Svelte](https://img.shields.io/badge/Svelte-5.x-FF3E00.svg?logo=svelte&logoColor=white)](https://svelte.dev)
[![Python](https://img.shields.io/badge/Python-3.11+-3776AB.svg?logo=python&logoColor=white)](https://python.org)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg?logo=docker&logoColor=white)](docker-compose.yml)

> **Web-Search.org** is an open-source, privacy-first, community-driven search engine monorepo powered by **Laravel 12**, **Svelte 5**, and a high-throughput **Python AsyncIO** distributed crawler.

- 🌐 GitHub Organization: [https://github.com/web-search-org](https://github.com/web-search-org)
- 🚀 Search Engine: [http://localhost:8000](http://localhost:8000)
- 📥 Submit New Websites: [http://localhost:8000/submit](http://localhost:8000/submit)
- 🧭 Search Console: [http://localhost:8000/console](http://localhost:8000/console)
- 📊 Open Stats & Telemetry: [http://localhost:8000/stats](http://localhost:8000/stats)

---

## 🌟 Key Features

- 🔒 **Zero Tracking & Privacy-First**: No cookie profiling, no user tracking, no query monetization.
- ⚡ **Ultra-Fast Search UI**: Built with **Svelte 5** and **Inertia.js**, delivering instant search responses, category filters, and keyboard navigation (`/`).
- 📥 **Submit New Websites UI**: Public submission interface to queue new websites, blogs, documentation sites, and sitemaps for instant indexing.
- 🧭 **Web-Search Console**: Open-source Google Search Console alternative (URL Inspection, SERP CTR/Position Performance, XML Sitemap submission, and DNS/Meta domain ownership verification).
- 🤖 **Distributed Async Crawler**: Python 3.11+ crawler with automated `robots.txt` compliance, polite rate limiting, HTML metadata extraction, and content deduplication.
- 🧠 **Multi-Signal Ranking**: Combines Okapi **BM25**, link-graph **PageRank**, and domain authority metrics.
- 🧮 **Instant Answers**: Instant calculation engine (e.g. `25 * 4`, math expressions, network diagnostics, UTC clock).
- 📊 **Open Crawler Control Panel**: Live web UI for submitting seed URLs, tracking crawl depth, and monitoring index health.
- 🛠️ **Developer SDKs**: Official **TypeScript** (`@web-search/sdk`) and **PHP** (`websearch/sdk`) client libraries.

---

## 📁 Monorepo Structure

```
web-search.org/
├── apps/
│   ├── web/                    # Main Search UI, Search Console & REST API (Laravel 12 + Svelte 5 + Inertia)
│   │   ├── app/                # Controllers, Models, Services (Search, Console, Crawler)
│   │   ├── resources/js/       # Svelte 5 Pages: Home, Search, SubmitSite, Console, Crawler, Stats, Docs
│   │   └── routes/             # Web & API routes
│   ├── crawler/                # Asynchronous Distributed Web Crawler (Python 3.11+)
│   │   ├── src/crawler/        # Engine, Frontier, Fetcher, Extractor, Dedup, Pipeline
│   │   └── tests/              # Pytest crawler test suite
│   └── indexer/                # Ranking & NLP Pipeline (BM25, PageRank, Tokenizer)
│       └── src/indexer/        # Okapi BM25 & Power-Iteration PageRank algorithms
├── packages/
│   ├── shared-types/           # Shared TypeScript contracts, search & crawl schemas (UUIDs)
│   ├── sdk-ts/                 # Official TypeScript / JavaScript Client SDK
│   └── sdk-php/                # Official PHP Client SDK
├── infra/
│   ├── docker/                 # Dockerfiles for Nginx, PHP-FPM, Crawler
│   └── k8s/                    # Kubernetes manifests
├── docker-compose.yml          # Complete local orchestration (Web, Nginx, Crawler, Redis, Meilisearch)
├── pnpm-workspace.yaml         # PNPM workspace configuration
└── Makefile                    # Common developer shortcuts
```

---

## 🚀 Quick Start

### 1. Prerequisites
- **Node.js** >= 20.x & **pnpm** >= 9.x
- **PHP** >= 8.2 & **Composer**
- **Python** >= 3.11

### 2. Installation & Setup
Run the unified setup command with `make`:
```bash
make setup
```

Or step-by-step:
```bash
# 1. Install JS/TS workspace dependencies
pnpm install

# 2. Install Laravel backend dependencies
cd apps/web && composer install

# 3. Initialize SQLite database
php artisan migrate

# 4. Build frontend assets
pnpm build
```

### 3. Run Development Servers
```bash
# Start Laravel HTTP backend (Terminal 1)
cd apps/web && php artisan serve

# Start Vite live reload (Terminal 2)
pnpm dev
```
Open [http://localhost:8000](http://localhost:8000) in your browser!

---

## 🐳 Docker Orchestration

Launch the full stack (Nginx, Laravel, Redis, Crawler, Meilisearch) with one command:
```bash
docker compose up -d
```

- Search Engine UI: [http://localhost:8000](http://localhost:8000)
- Submit Websites: [http://localhost:8000/submit](http://localhost:8000/submit)
- Search Console: [http://localhost:8000/console](http://localhost:8000/console)
- Crawler Dashboard: [http://localhost:8000/crawler](http://localhost:8000/crawler)
- Open Stats: [http://localhost:8000/stats](http://localhost:8000/stats)

---

## 🕸️ Running the Web Crawler

Index any website from the CLI:
```bash
cd apps/crawler
pip install -r requirements.txt
python -m crawler run --seed "https://news.ycombinator.com" --depth 2 --max-pages 100 --concurrency 5
```

---

## 🔌 Developer REST API

### Search Endpoint
```http
GET /api/v1/search?q=laravel+svelte&category=tech&page=1
```

### Submit Website / Crawl Request Endpoint
```http
POST /api/v1/submit
Content-Type: application/json

{
  "url": "https://svelte.dev",
  "category": "tech",
  "max_pages": 50,
  "is_sitemap": false
}
```

### Search Console URL Inspection Endpoint
```http
GET /api/v1/console/inspect?url=https://svelte.dev
```

---

## 📦 Client SDKs

### TypeScript / JavaScript SDK (`@web-search/sdk`)
```typescript
import { WebSearchClient } from '@web-search/sdk';

const client = new WebSearchClient({ baseUrl: 'http://localhost:8000' });
const response = await client.search({ q: 'open source search engine' });

console.log(response.results);
```

### PHP SDK (`websearch/sdk`)
```php
use WebSearch\Sdk\Client;

$client = new Client('http://localhost:8000');
$response = $client->search(['q' => 'laravel inertia svelte']);

print_r($response['results']);
```

---

## 🧪 Testing

```bash
# Test TypeScript packages & types
pnpm test

# Test Laravel backend
cd apps/web && php artisan test

# Test Python crawler & ranker
cd apps/crawler && python -m unittest discover tests
cd apps/indexer && python -m unittest discover tests
```

---

## 📄 License

This project is licensed under the [AGPL-3.0 License](LICENSE).
Official repository: [https://github.com/web-search-org](https://github.com/web-search-org)
