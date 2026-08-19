import asyncio
import re
from urllib.parse import urlparse, urlunparse, urljoin, urldefrag
from typing import Set, Tuple, Optional, Dict
import time

def normalize_url(url: str) -> str:
    """Normalize a URL to prevent duplicate visits of equivalent URLs."""
    url, _ = urldefrag(url.strip())
    parsed = urlparse(url)
    scheme = parsed.scheme.lower()
    if scheme not in ("http", "https"):
        return ""
    
    netloc = parsed.netloc.lower()
    # Strip standard ports
    if netloc.endswith(":80") and scheme == "http":
        netloc = netloc[:-3]
    elif netloc.endswith(":443") and scheme == "https":
        netloc = netloc[:-4]

    path = parsed.path or "/"
    # Clean up double slashes
    path = re.sub(r"/+", "/", path)
    
    # Sort query parameters for consistency
    query = parsed.query
    if query:
        params = sorted(query.split("&"))
        # Filter tracking params (utm_*, fbclid, etc.)
        filtered_params = [
            p for p in params 
            if not re.match(r"^(utm_|ref|fbclid|gclid|_ga|_gl)", p, re.IGNORECASE)
        ]
        query = "&".join(filtered_params)

    return urlunparse((scheme, netloc, path, "", query, ""))

def get_domain(url: str) -> str:
    parsed = urlparse(url)
    return parsed.netloc.lower()

class URLFrontier:
    def __init__(self, stay_on_domain: bool = True, allow_subdomains: bool = True):
        self.queue: asyncio.Queue[Tuple[str, int]] = asyncio.Queue()
        self.seen_urls: Set[str] = set()
        self.seed_domains: Set[str] = set()
        self.stay_on_domain = stay_on_domain
        self.allow_subdomains = allow_subdomains
        self.last_domain_access: Dict[str, float] = {}

    def add_seed(self, url: str) -> bool:
        normalized = normalize_url(url)
        if not normalized:
            return False
        domain = get_domain(normalized)
        self.seed_domains.add(domain)
        self.seen_urls.add(normalized)
        self.queue.put_nowait((normalized, 0))
        return True

    def add_url(self, url: str, depth: int, base_url: Optional[str] = None) -> bool:
        if base_url:
            url = urljoin(base_url, url)
        
        normalized = normalize_url(url)
        if not normalized or normalized in self.seen_urls:
            return False

        domain = get_domain(normalized)
        if self.stay_on_domain and not self._is_domain_allowed(domain):
            return False

        self.seen_urls.add(normalized)
        self.queue.put_nowait((normalized, depth))
        return True

    def _is_domain_allowed(self, domain: str) -> bool:
        for seed_domain in self.seed_domains:
            if domain == seed_domain:
                return True
            if self.allow_subdomains and domain.endswith("." + seed_domain):
                return True
        return False

    async def get_next(self) -> Tuple[str, int]:
        return await self.queue.get()

    def task_done(self) -> None:
        self.queue.task_done()

    @property
    def empty(self) -> bool:
        return self.queue.empty()

    @property
    def size(self) -> int:
        return self.queue.qsize()

    @property
    def total_discovered(self) -> int:
        return len(self.seen_urls)
