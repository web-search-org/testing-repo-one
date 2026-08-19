import unittest
import sys
import os

# Add src directory to path
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), "../src")))

from indexer.tokenizer import Tokenizer
from indexer.bm25 import BM25Ranker
from indexer.pagerank import PageRankCalculator
from indexer.service import IndexingService

class TestIndexer(unittest.TestCase):
    def test_tokenizer(self):
        tokens = Tokenizer.tokenize("Web-Search.org is an amazing open-source search engine!")
        self.assertIn("web-search.org", tokens)
        self.assertIn("amazing", tokens)
        self.assertIn("open-source", tokens)
        self.assertIn("search", tokens)
        self.assertIn("engine", tokens)
        self.assertNotIn("an", tokens)  # stop word removed

    def test_bm25_ranking(self):
        ranker = BM25Ranker()
        ranker.add_document("doc1", "The quick brown fox jumps over the lazy dog.")
        ranker.add_document("doc2", "Open source search engines and web crawlers are powerful.")
        ranker.add_document("doc3", "Learn how to build a search engine with Python and Laravel.")

        results = ranker.score("search engine")
        self.assertGreaterEqual(len(results), 2)
        top_doc_id, top_score = results[0]
        self.assertIn(top_doc_id, ("doc2", "doc3"))
        self.assertGreater(top_score, 0)

    def test_pagerank(self):
        graph = {
            "https://a.com": ["https://b.com", "https://c.com"],
            "https://b.com": ["https://c.com"],
            "https://c.com": ["https://a.com"],
            "https://d.com": ["https://c.com"],
        }
        calc = PageRankCalculator()
        ranks = calc.calculate(graph)
        # Page C should have the highest PageRank because everyone links to it
        self.assertGreater(ranks["https://c.com"], ranks["https://b.com"])
        self.assertGreater(ranks["https://c.com"], ranks["https://d.com"])

    def test_indexing_service(self):
        service = IndexingService()
        service.index_page("1", "Laravel Framework", "The PHP framework for web artisans with elegant syntax.", "https://laravel.com", ["https://svelte.dev"])
        service.index_page("2", "Svelte UI", "Cybernetically enhanced web apps with fast reactivity.", "https://svelte.dev", ["https://laravel.com"])
        service.recompute_pagerank()

        res = service.search("laravel framework")
        self.assertGreater(len(res), 0)
        self.assertEqual(res[0]["doc"]["id"], "1")

if __name__ == "__main__":
    unittest.main()
