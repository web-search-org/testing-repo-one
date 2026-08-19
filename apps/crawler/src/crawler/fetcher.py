import asyncio
import httpx
from typing import Optional, Dict
from dataclasses import dataclass
import time

@dataclass
class FetchResult:
    url: str
    status_code: int
    content_type: str
    content_bytes: bytes
    response_time_ms: float
    headers: Dict[str, str]
    error: Optional[str] = None

class AsyncFetcher:
    def __init__(self, client: httpx.AsyncClient, max_size_bytes: int = 5 * 1024 * 1024):
        self.client = client
        self.max_size_bytes = max_size_bytes

    async def fetch(self, url: str) -> FetchResult:
        start_time = time.perf_counter()
        try:
            response = await self.client.get(
                url,
                follow_redirects=True,
                headers={"Accept-Encoding": "gzip, deflate, br"}
            )
            elapsed_ms = (time.perf_counter() - start_time) * 1000.0

            content_type = response.headers.get("content-type", "").split(";")[0].strip().lower()
            
            # Guard against oversized files
            content_bytes = response.content
            if len(content_bytes) > self.max_size_bytes:
                content_bytes = content_bytes[:self.max_size_bytes]

            return FetchResult(
                url=str(response.url),
                status_code=response.status_code,
                content_type=content_type,
                content_bytes=content_bytes,
                response_time_ms=elapsed_ms,
                headers=dict(response.headers),
                error=None
            )
        except httpx.TimeoutException:
            elapsed_ms = (time.perf_counter() - start_time) * 1000.0
            return FetchResult(
                url=url,
                status_code=0,
                content_type="",
                content_bytes=b"",
                response_time_ms=elapsed_ms,
                headers={},
                error="Timeout exceeded"
            )
        except Exception as e:
            elapsed_ms = (time.perf_counter() - start_time) * 1000.0
            return FetchResult(
                url=url,
                status_code=0,
                content_type="",
                content_bytes=b"",
                response_time_ms=elapsed_ms,
                headers={},
                error=str(e)
            )
