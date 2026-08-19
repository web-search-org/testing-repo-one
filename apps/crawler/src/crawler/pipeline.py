import asyncio
from typing import Optional, Dict, Any
from .extractor import ExtractedDocument

class IngestionPipeline:
    def __init__(self, api_endpoint: Optional[str] = None, api_key: Optional[str] = None):
        self.api_endpoint = api_endpoint
        self.api_key = api_key
        self._client = None

    @property
    def client(self):
        if self._client is None:
            import httpx
            self._client = httpx.AsyncClient(timeout=10.0)
        return self._client

    async def ingest_document(self, doc: ExtractedDocument, response_time_ms: float, status_code: int) -> bool:
        if not self.api_endpoint:
            return True

        payload: Dict[str, Any] = {
            "url": doc.url,
            "domain": doc.domain,
            "title": doc.title,
            "description": doc.meta_description,
            "keywords": doc.meta_keywords,
            "headings": doc.headings,
            "content": doc.clean_text,
            "language": doc.language,
            "favicon_url": doc.favicon_url,
            "og_image": doc.og_image,
            "canonical_url": doc.canonical_url,
            "outbound_links_count": len(doc.outbound_links),
            "content_hash": doc.content_hash,
            "status_code": status_code,
            "response_time_ms": response_time_ms
        }

        headers = {
            "Content-Type": "application/json",
            "Accept": "application/json"
        }
        if self.api_key:
            headers["Authorization"] = f"Bearer {self.api_key}"

        try:
            res = await self.client.post(self.api_endpoint, json=payload, headers=headers)
            return res.status_code in (200, 201)
        except Exception:
            return False

    async def close(self):
        if self._client is not None:
            await self._client.aclose()
