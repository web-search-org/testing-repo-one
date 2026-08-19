from dataclasses import dataclass, field
from typing import List, Optional

@dataclass
class CrawlerConfig:
    user_agent: str = "WebSearchBot/1.0 (+https://web-search.org/bot.html; open-source search engine)"
    max_depth: int = 3
    max_pages: int = 500
    concurrency: int = 10
    domain_delay_ms: int = 500
    request_timeout_seconds: float = 15.0
    respect_robots_txt: bool = True
    allow_subdomains: bool = True
    stay_on_domain: bool = True
    max_content_size_bytes: int = 5 * 1024 * 1024
    api_endpoint: Optional[str] = "http://localhost:8000/api/v1/crawl/ingest"
    api_key: Optional[str] = None
    allowed_content_types: List[str] = field(
        default_factory=lambda: ["text/html", "application/xhtml+xml"]
    )
