# Contributing to Web-Search.org

Thank you for your interest in contributing to **Web-Search.org**, the open-source, privacy-first search engine!

## Development Setup

### Prerequisites
- **PHP** >= 8.2 (with `pdo_sqlite`, `mbstring`, `curl`)
- **Composer** >= 2.x
- **Node.js** >= 20.x
- **pnpm** >= 9.x
- **Python** >= 3.11

### Initializing the Monorepo

1. Clone the repository and install dependencies:
   ```bash
   pnpm install
   cd apps/web && composer install
   ```

2. Set up environment and database:
   ```bash
   cd apps/web
   cp .env.example .env
   php artisan key:generate
   touch database/database.sqlite
   php artisan migrate --seed
   ```

3. Build frontend and shared packages:
   ```bash
   pnpm build
   ```

4. Start development servers:
   ```bash
   # In terminal 1: Start Laravel backend
   cd apps/web && php artisan serve

   # In terminal 2: Start Vite live reload
   pnpm dev
   ```

---

## Running the Crawler

To launch crawler workers locally:
```bash
cd apps/crawler
pip install -r requirements.txt
python -m crawler run --seed "https://news.ycombinator.com" --max-pages 50 --concurrency 5
```

---

## Running Tests

- **TypeScript packages**: `pnpm test`
- **Laravel backend**: `cd apps/web && php artisan test`
- **Python crawler & indexer**: `cd apps/crawler && pytest`

---

## Pull Request Guidelines

1. Ensure all linting and test suites pass before submitting.
2. Keep PRs focused on a single feature or bugfix.
3. Include relevant unit tests for new crawler extractors or ranker algorithms.
