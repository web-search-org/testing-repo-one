# 🌐 Web-Search.org

[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL_3.0-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20.svg?logo=laravel&logoColor=white)](https://laravel.com)
[![Svelte](https://img.shields.io/badge/Svelte-5.x-FF3E00.svg?logo=svelte&logoColor=white)](https://svelte.dev)
[![Python](https://img.shields.io/badge/Python-3.11+-3776AB.svg?logo=python&logoColor=white)](https://python.org)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED.svg?logo=docker&logoColor=white)](docker-compose.yml)

> **Web-Search.org** is an open-source, privacy-first, community-driven search engine monorepo powered by **Laravel 12**, **Svelte 5**, and a high-throughput **Python AsyncIO** distributed crawler.

---

## 🌟 Key Features

- 🔒 **Zero Tracking & Privacy-First**: No cookie profiling, no user tracking, no query monetization.
- ⚡ **Ultra-Fast Search UI**: Built with **Svelte 5** and **Inertia.js**, delivering instant search responses, category filters, and keyboard navigation (`/`).
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
│   ├── web/                    # Main Search UI & REST API (Laravel 12 + Svelte 5 + Inertia)
│   │   ├── app/                # Controllers, Models, SearchService, CrawlerService
│   │   ├── resources/js/       # Svelte 5 Pages: Home, Search, Crawler Dashboard, Stats, Docs
│   │   └── routes/             # Web & API routes
│   ├── crawler/                # Asynchronous Distributed Web Crawler (Python 3.11+)
│   │   ├── src/crawler/        # Engine, Frontier, Fetcher, Extractor, Dedup, Pipeline
│   │   └── tests/              # Pytest crawler test suite
│   └── indexer/                # Ranking & NLP Pipeline (BM25, PageRank, Tokenizer)
│       └── src/indexer/        # Okapi BM25 & Power-Iteration PageRank algorithms
├── packages/
│   ├── shared-types/           # Shared TypeScript contracts, search & crawl schemas
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

# 3. Initialize SQLite database and seed initial index
php artisan migrate --seed

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
- Crawler Dashboard: [http://localhost:8000/crawler](http://localhost:8000/crawler)
- Meilisearch Engine: [http://localhost:7700](http://localhost:7700)

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
**Response:**
```json
{
  "query": "laravel svelte",
  "totalHits": 4,
  "page": 1,
  "totalPages": 1,
  "executionTimeMs": 1.25,
  "results": [
    {
      "id": 1,
      "url": "https://svelte.dev",
      "domain": "svelte.dev",
      "title": "Svelte • Cybernetically enhanced web apps",
      "snippet": "Svelte is a radical new approach to building user interfaces...",
      "favicon": "https://www.google.com/s2/favicons?domain=svelte.dev&sz=64",
      "rankScore": 9.6
    }
  ]
}
```

### Autocomplete Suggest Endpoint
```http
GET /api/v1/suggest?q=lar
```

### Queue Crawl Job
```http
POST /api/v1/crawl
Content-Type: application/json

{
  "url": "https://example.com",
  "maxDepth": 3,
  "maxPages": 200
}
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
cd apps/crawler && pytest
```

---

## 📄 License

This project is licensed under the [AGPL-3.0 License](LICENSE).
