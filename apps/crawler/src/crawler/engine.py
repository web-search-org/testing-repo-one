import asyncio
import time
from typing import List, Optional
from urllib.parse import urlparse

from .config import CrawlerConfig
from .frontier import URLFrontier, get_domain
from .dedup import Deduplicator
from .pipeline import IngestionPipeline

try:
    from rich.console import Console
    console = Console()
    def log_info(msg: str):
        console.print(msg)
except ImportError:
    def log_info(msg: str):
        print(msg)

class AsyncCrawlerEngine:
    def __init__(self, config: CrawlerConfig):
        self.config = config
        self.frontier = URLFrontier(
            stay_on_domain=config.stay_on_domain,
            allow_subdomains=config.allow_subdomains
        )
        self.deduplicator = Deduplicator()
        self.pipeline = IngestionPipeline(
            api_endpoint=config.api_endpoint,
            api_key=config.api_key
        )
        self.pages_crawled = 0
        self.pages_indexed = 0
        self.errors_count = 0
        self.is_running = False
        self.domain_last_visited = {}

    async def crawl(self, seed_urls: List[str]):
        import httpx
        from .robots import RobotsTxtManager
        from .fetcher import AsyncFetcher
        from .extractor import PageExtractor

        self.is_running = True
        for url in seed_urls:
            self.frontier.add_seed(url)

        log_info(f"Web-Search.org Crawler Starting...")
        log_info(f"  Seeds: {seed_urls}")
        log_info(f"  Concurrency: {self.config.concurrency} | Max Depth: {self.config.max_depth} | Max Pages: {self.config.max_pages}")

        limits = httpx.Limits(max_keepalive_connections=20, max_connections=50)
        async with httpx.AsyncClient(
            headers={"User-Agent": self.config.user_agent},
            limits=limits,
            timeout=self.config.request_timeout_seconds
        ) as client:
            robots_mgr = RobotsTxtManager(self.config.user_agent, client) if self.config.respect_robots_txt else None
            fetcher = AsyncFetcher(client, self.config.max_content_size_bytes)

            workers = [
                asyncio.create_task(self._worker(i, fetcher, robots_mgr))
                for i in range(self.config.concurrency)
            ]

            monitor = asyncio.create_task(self._progress_monitor())

            try:
                while self.is_running and self.pages_crawled < self.config.max_pages:
                    if self.frontier.empty and self.frontier.queue._unfinished_tasks == 0:
                        break
                    await asyncio.sleep(0.5)
            finally:
                self.is_running = False
                for w in workers:
                    w.cancel()
                monitor.cancel()
                await self.pipeline.close()

        log_info(f"\nCrawl Complete!")
        log_info(f"  Pages Crawled: {self.pages_crawled} | Indexed: {self.pages_indexed} | Errors: {self.errors_count} | Total Seen: {self.frontier.total_discovered}")

    async def _worker(self, worker_id: int, fetcher, robots_mgr):
        from .extractor import PageExtractor

        while self.is_running and self.pages_crawled < self.config.max_pages:
            try:
                try:
                    url, depth = await asyncio.wait_for(self.frontier.get_next(), timeout=2.0)
                except asyncio.TimeoutError:
                    continue

                domain = get_domain(url)
                now = time.time()
                last_time = self.domain_last_visited.get(domain, 0)
                delay_needed = (self.config.domain_delay_ms / 1000.0) - (now - last_time)
                if delay_needed > 0:
                    await asyncio.sleep(delay_needed)
                self.domain_last_visited[domain] = time.time()

                if robots_mgr:
                    can_fetch = await robots_mgr.can_fetch(url)
                    if not can_fetch:
                        self.frontier.task_done()
                        continue

                result = await fetcher.fetch(url)
                self.pages_crawled += 1

                if result.error or result.status_code != 200:
                    self.errors_count += 1
                    self.frontier.task_done()
                    continue

                if "html" not in result.content_type:
                    self.frontier.task_done()
                    continue

                doc = PageExtractor.extract(result.content_bytes, result.url)
                if not doc:
                    self.frontier.task_done()
                    continue

                doc.content_hash = Deduplicator.compute_hash(doc.clean_text)
                if self.deduplicator.is_duplicate(doc.content_hash):
                    self.frontier.task_done()
                    continue

                await self.pipeline.ingest_document(doc, result.response_time_ms, result.status_code)
                self.pages_indexed += 1

                if depth < self.config.max_depth:
                    for link in doc.outbound_links:
                        self.frontier.add_url(link, depth + 1, base_url=result.url)

                self.frontier.task_done()
            except asyncio.CancelledError:
                break
            except Exception:
                self.errors_count += 1
                try:
                    self.frontier.task_done()
                except Exception:
                    pass

    async def _progress_monitor(self):
        while self.is_running:
            await asyncio.sleep(3.0)
            log_info(
                f"[Crawled: {self.pages_crawled} | Indexed: {self.pages_indexed} | Queue: {self.frontier.size} | Errors: {self.errors_count}]"
            )
