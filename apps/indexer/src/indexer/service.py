from typing import List, Dict, Any, Optional
from .tokenizer import Tokenizer
from .bm25 import BM25Ranker
from .pagerank import PageRankCalculator

class IndexingService:
    def __init__(self):
        self.bm25 = BM25Ranker()
        self.graph: Dict[str, List[str]] = {}
        self.doc_store: Dict[str, Dict[str, Any]] = {}
        self.page_ranks: Dict[str, float] = {}

    def index_page(self, doc_id: str, title: str, content: str, url: str, outbound_links: List[str], metadata: Optional[Dict[str, Any]] = None):
        combined_text = f"{title} {title} {content}"  # Weight title 2x
        self.bm25.add_document(doc_id, combined_text)
        self.graph[url] = outbound_links
        self.doc_store[doc_id] = {
            "id": doc_id,
            "title": title,
            "content": content,
            "url": url,
            "metadata": metadata or {}
        }

    def recompute_pagerank(self):
        calc = PageRankCalculator()
        self.page_ranks = calc.calculate(self.graph)

    def search(self, query: str, top_k: int = 10, bm25_weight: float = 0.7, pagerank_weight: float = 0.3) -> List[Dict[str, Any]]:
        bm25_results = dict(self.bm25.score(query, top_k=top_k * 2))
        if not bm25_results:
            return []

        # Max BM25 score for normalization
        max_bm25 = max(bm25_results.values()) if bm25_results else 1.0

        combined_scores = []
        for doc_id, b_score in bm25_results.items():
            doc = self.doc_store[doc_id]
            url = doc["url"]
            pr_score = self.page_ranks.get(url, 0.001)

            norm_bm25 = b_score / max(0.0001, max_bm25)
            final_score = (bm25_weight * norm_bm25) + (pagerank_weight * pr_score * 100)

            combined_scores.append({
                "doc": doc,
                "score": round(final_score, 4),
                "bm25_score": round(b_score, 4),
                "pagerank_score": round(pr_score, 6)
            })

        combined_scores.sort(key=lambda x: x["score"], reverse=True)
        return combined_scores[:top_k]
