import math
from typing import List, Dict, Tuple
from collections import Counter
from .tokenizer import Tokenizer

class BM25Ranker:
    """Okapi BM25 relevance score calculator."""
    
    def __init__(self, k1: float = 1.5, b: float = 0.75):
        self.k1 = k1
        self.b = b
        self.doc_lengths: Dict[str, int] = {}
        self.avg_doc_length: float = 0.0
        self.corpus_size: int = 0
        self.doc_freqs: Dict[str, int] = {}  # term -> number of documents containing term
        self.term_freqs: Dict[str, Counter] = {}  # doc_id -> Counter(terms)

    def add_document(self, doc_id: str, text: str):
        tokens = Tokenizer.tokenize(text)
        length = len(tokens)
        self.doc_lengths[doc_id] = length
        
        tf = Counter(tokens)
        self.term_freqs[doc_id] = tf
        
        for term in tf.keys():
            self.doc_freqs[term] = self.doc_freqs.get(term, 0) + 1

        self.corpus_size = len(self.doc_lengths)
        self.avg_doc_length = sum(self.doc_lengths.values()) / max(1, self.corpus_size)

    def idf(self, term: str) -> float:
        df = self.doc_freqs.get(term, 0)
        # Standard Lucene/BM25 IDF formula
        return math.log(1 + (self.corpus_size - df + 0.5) / (df + 0.5))

    def score(self, query: str, top_k: int = 10) -> List[Tuple[str, float]]:
        query_tokens = Tokenizer.tokenize(query)
        if not query_tokens or self.corpus_size == 0:
            return []

        scores: Dict[str, float] = {}

        for doc_id, tf in self.term_freqs.items():
            doc_len = self.doc_lengths[doc_id]
            doc_score = 0.0

            for token in query_tokens:
                if token not in tf:
                    continue
                
                f = tf[token]
                idf_val = self.idf(token)
                numerator = f * (self.k1 + 1)
                denominator = f + self.k1 * (1 - self.b + self.b * (doc_len / self.avg_doc_length))
                doc_score += idf_val * (numerator / denominator)

            if doc_score > 0:
                scores[doc_id] = doc_score

        # Sort descending by score
        ranked = sorted(scores.items(), key=lambda x: x[1], reverse=True)
        return ranked[:top_k]
