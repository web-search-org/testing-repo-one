import unittest
import sys
import os

# Add src directory to path
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), "../src")))

from crawler.frontier import normalize_url, URLFrontier
from crawler.dedup import Deduplicator

class TestCrawler(unittest.TestCase):
    def test_normalize_url(self):
        self.assertEqual(normalize_url("https://example.com/foo#bar"), "https://example.com/foo")
        self.assertEqual(normalize_url("HTTPS://EXAMPLE.COM/FOO/"), "https://example.com/FOO/")
        self.assertEqual(normalize_url("https://example.com:443/test"), "https://example.com/test")
        self.assertEqual(normalize_url("http://example.com:80/test"), "http://example.com/test")
        # Strips tracking params
        self.assertEqual(normalize_url("https://example.com/article?utm_source=twitter&id=123"), "https://example.com/article?id=123")

    def test_url_frontier(self):
        frontier = URLFrontier(stay_on_domain=True, allow_subdomains=True)
        self.assertTrue(frontier.add_seed("https://web-search.org"))
        self.assertEqual(frontier.total_discovered, 1)
        # Adding duplicate
        self.assertFalse(frontier.add_url("https://web-search.org", 1))
        # Adding same domain
        self.assertTrue(frontier.add_url("https://web-search.org/about", 1))
        # Adding allowed subdomain
        self.assertTrue(frontier.add_url("https://docs.web-search.org/api", 1))
        # External domain disallowed
        self.assertFalse(frontier.add_url("https://google.com/search", 1))

    def test_deduplicator(self):
        dedup = Deduplicator()
        text = "Hello world! This is a test document."
        h = Deduplicator.compute_hash(text)
        self.assertFalse(dedup.is_duplicate(h))
        self.assertTrue(dedup.is_duplicate(h))

if __name__ == "__main__":
    unittest.main()
