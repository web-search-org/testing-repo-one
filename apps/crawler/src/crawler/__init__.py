"""
Web-Search.org Crawler Package
"""

from .config import CrawlerConfig
from .engine import AsyncCrawlerEngine
from .extractor import ExtractedDocument, PageExtractor

__all__ = ["CrawlerConfig", "AsyncCrawlerEngine", "PageExtractor", "ExtractedDocument"]
__version__ = "0.1.0"
