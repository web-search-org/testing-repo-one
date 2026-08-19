import asyncio
import click
from typing import List
from .config import CrawlerConfig
from .engine import AsyncCrawlerEngine

@click.group()
def main():
    """Web-Search.org High-Performance Crawler CLI"""
    pass

@main.command()
@click.option("--seed", "-s", multiple=True, required=True, help="Seed URL(s) to start crawling from")
@click.option("--depth", "-d", default=3, help="Maximum crawl depth")
@click.option("--max-pages", "-m", default=200, help="Maximum pages to crawl")
@click.option("--concurrency", "-c", default=10, help="Concurrent async workers")
@click.option("--delay", default=300, help="Per-domain polite delay in ms")
@click.option("--no-robots", is_flag=True, default=False, help="Disable robots.txt parsing")
@click.option("--api-endpoint", default="http://localhost:8000/api/v1/crawl/ingest", help="Ingest API URL")
@click.option("--api-key", default=None, help="API Key for ingestion")
def run(seed: List[str], depth: int, max_pages: int, concurrency: int, delay: int, no_robots: bool, api_endpoint: str, api_key: str):
    """Run the web crawler with specified seeds."""
    config = CrawlerConfig(
        max_depth=depth,
        max_pages=max_pages,
        concurrency=concurrency,
        domain_delay_ms=delay,
        respect_robots_txt=not no_robots,
        api_endpoint=api_endpoint,
        api_key=api_key
    )

    engine = AsyncCrawlerEngine(config)
    asyncio.run(engine.crawl(list(seed)))

if __name__ == "__main__":
    main()
