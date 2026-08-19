import asyncio
import httpx
from urllib.robotparser import RobotFileParser
from urllib.parse import urlparse
from typing import Dict, Optional
import time

class RobotsTxtManager:
    def __init__(self, user_agent: str, client: httpx.AsyncClient):
        self.user_agent = user_agent
        self.client = client
        self.parsers: Dict[str, RobotFileParser] = {}
        self.crawl_delays: Dict[str, float] = {}
        self._lock = asyncio.Lock()

    async def can_fetch(self, url: str) -> bool:
        parsed = urlparse(url)
        domain = parsed.netloc.lower()
        parser = await self._get_parser(parsed.scheme, domain)
        if parser is None:
            return True
        return parser.can_fetch(self.user_agent, url)

    async def get_crawl_delay(self, url: str) -> Optional[float]:
        parsed = urlparse(url)
        domain = parsed.netloc.lower()
        await self._get_parser(parsed.scheme, domain)
        return self.crawl_delays.get(domain)

    async def _get_parser(self, scheme: str, domain: str) -> Optional[RobotFileParser]:
        if domain in self.parsers:
            return self.parsers[domain]

        async with self._lock:
            if domain in self.parsers:
                return self.parsers[domain]

            robots_url = f"{scheme}://{domain}/robots.txt"
            parser = RobotFileParser()
            try:
                response = await self.client.get(robots_url, timeout=5.0)
                if response.status_code == 200:
                    parser.parse(response.text.splitlines())
                    # Extract crawl delay if any
                    try:
                        delay = parser.crawl_delay(self.user_agent)
                        if delay:
                            self.crawl_delays[domain] = float(delay)
                    except Exception:
                        pass
                else:
                    parser.allow_all = True
            except Exception:
                # In case of network error, treat as allow_all but be polite
                parser.allow_all = True

            self.parsers[domain] = parser
            return parser
