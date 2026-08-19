.PHONY: setup dev build test crawl up down clean

setup:
	@echo "Installing root and package dependencies with PNPM..."
	pnpm install
	@echo "Installing Laravel backend dependencies with Composer..."
	cd apps/web && composer install
	@echo "Running SQLite database migrations and seeders..."
	cd apps/web && php artisan migrate --seed
	@echo "Building frontend and shared packages..."
	pnpm build
	@echo "✅ Web-Search.org Monorepo setup completed!"

dev:
	@echo "Starting development servers..."
	pnpm dev

serve:
	@echo "Starting Laravel HTTP server at http://localhost:8000"
	cd apps/web && php artisan serve --port 8000

crawl:
	@echo "Launching crawler worker..."
	cd apps/crawler && python3 -m crawler.cli run --seed https://news.ycombinator.com --max-pages 50

test:
	@echo "Running test suites across all apps and packages..."
	pnpm test
	cd apps/web && php artisan test

up:
	docker compose up -d

down:
	docker compose down

clean:
	rm -rf node_modules apps/web/node_modules packages/*/node_modules
	rm -rf dist packages/*/dist apps/web/public/build
