import re
from typing import List, Set

STOP_WORDS: Set[str] = {
    "a", "about", "above", "after", "again", "against", "all", "am", "an", "and", "any", "are", "as", "at",
    "be", "because", "been", "before", "being", "below", "between", "both", "but", "by", "could", "did", "do",
    "does", "doing", "down", "during", "each", "few", "for", "from", "further", "had", "has", "have", "having",
    "he", "her", "here", "hers", "herself", "him", "himself", "his", "how", "i", "if", "in", "into", "is", "it",
    "its", "itself", "just", "me", "more", "most", "my", "myself", "no", "nor", "not", "now", "of", "off", "on",
    "once", "only", "or", "other", "our", "ours", "ourselves", "out", "over", "own", "s", "same", "she", "should",
    "so", "some", "such", "than", "that", "the", "their", "theirs", "them", "themselves", "then", "there", "these",
    "they", "this", "those", "through", "to", "too", "under", "until", "up", "very", "was", "we", "were", "what",
    "when", "where", "which", "while", "who", "whom", "why", "with", "would", "you", "your", "yours", "yourself",
    "yourselves"
}

class Tokenizer:
    @staticmethod
    def tokenize(text: str, remove_stopwords: bool = True) -> List[str]:
        if not text:
            return []
        
        # Lowercase and extract alphanumeric tokens
        tokens = re.findall(r"\b[a-zA-Z0-9_\-\.\@]{2,}\b", text.lower())
        
        if remove_stopwords:
            tokens = [t for t in tokens if t not in STOP_WORDS]
            
        return tokens

    @staticmethod
    def ngrams(tokens: List[str], n: int = 2) -> List[str]:
        return [" ".join(tokens[i:i+n]) for i in range(len(tokens) - n + 1)]
