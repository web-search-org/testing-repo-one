import hashlib
import re
from typing import Set

class Deduplicator:
    def __init__(self):
        self.seen_hashes: Set[str] = set()

    @staticmethod
    def compute_hash(text: str) -> str:
        """Compute a fast normalized sha256 hash of clean text for deduplication."""
        # Normalize text to ignore trivial differences like spaces and casing
        normalized = re.sub(r"\W+", "", text.lower())
        return hashlib.sha256(normalized.encode("utf-8")).hexdigest()

    def is_duplicate(self, content_hash: str) -> bool:
        if content_hash in self.seen_hashes:
            return True
        self.seen_hashes.add(content_hash)
        return False
