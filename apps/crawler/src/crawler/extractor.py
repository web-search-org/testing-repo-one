import re
from typing import List, Dict, Optional, Set
from dataclasses import dataclass, field
from urllib.parse import urljoin, urlparse

@dataclass
class ExtractedDocument:
    url: str
    domain: str
    title: str = ""
    meta_description: str = ""
    meta_keywords: List[str] = field(default_factory=list)
    headings: List[str] = field(default_factory=list)
    clean_text: str = ""
    outbound_links: List[str] = field(default_factory=list)
    language: Optional[str] = None
    canonical_url: Optional[str] = None
    favicon_url: Optional[str] = None
    og_image: Optional[str] = None
    content_hash: str = ""

class PageExtractor:
    @staticmethod
    def extract(html_bytes: bytes, url: str) -> Optional[ExtractedDocument]:
        try:
            from bs4 import BeautifulSoup
        except ImportError:
            # Fallback regex parser if bs4 not installed
            return PageExtractor._fallback_extract(html_bytes, url)

        try:
            html_text = ""
            for encoding in ("utf-8", "latin-1", "iso-8859-1"):
                try:
                    html_text = html_bytes.decode(encoding)
                    break
                except UnicodeDecodeError:
                    continue
            
            if not html_text:
                return None

            soup = BeautifulSoup(html_text, "html.parser")

            for tag in soup(["script", "style", "noscript", "iframe", "svg", "nav", "footer"]):
                tag.decompose()

            title = ""
            if soup.title and soup.title.string:
                title = soup.title.string.strip()
            elif soup.find("meta", property="og:title"):
                title = soup.find("meta", property="og:title").get("content", "").strip()

            meta_desc = ""
            desc_tag = soup.find("meta", attrs={"name": re.compile(r"^description$", re.I)})
            if not desc_tag:
                desc_tag = soup.find("meta", property="og:description")
            if desc_tag and desc_tag.get("content"):
                meta_desc = desc_tag["content"].strip()

            meta_keywords: List[str] = []
            keywords_tag = soup.find("meta", attrs={"name": re.compile(r"^keywords$", re.I)})
            if keywords_tag and keywords_tag.get("content"):
                meta_keywords = [k.strip() for k in keywords_tag["content"].split(",") if k.strip()]

            headings = [h.get_text(strip=True) for h in soup.find_all(["h1", "h2", "h3"]) if h.get_text(strip=True)]

            lang = None
            html_tag = soup.find("html")
            if html_tag and html_tag.get("lang"):
                lang = html_tag["lang"].split("-")[0].lower()

            favicon_url = None
            icon_tag = soup.find("link", rel=re.compile(r"icon", re.I))
            if icon_tag and icon_tag.get("href"):
                favicon_url = urljoin(url, icon_tag["href"])
            else:
                parsed = urlparse(url)
                favicon_url = f"{parsed.scheme}://{parsed.netloc}/favicon.ico"

            og_img_tag = soup.find("meta", property="og:image")
            og_image = og_img_tag["content"].strip() if og_img_tag and og_img_tag.get("content") else None
            if og_image:
                og_image = urljoin(url, og_image)

            canonical_tag = soup.find("link", rel="canonical")
            canonical_url = urljoin(url, canonical_tag["href"].strip()) if canonical_tag and canonical_tag.get("href") else None

            raw_text = soup.get_text(separator=" ")
            clean_text = re.sub(r"\s+", " ", raw_text).strip()

            outbound_links: Set[str] = set()
            for a_tag in soup.find_all("a", href=True):
                href = a_tag["href"].strip()
                if href and not href.startswith(("#", "javascript:", "mailto:", "tel:")):
                    full_link = urljoin(url, href)
                    outbound_links.add(full_link)

            domain = urlparse(url).netloc.lower()

            return ExtractedDocument(
                url=url,
                domain=domain,
                title=title or domain,
                meta_description=meta_desc,
                meta_keywords=meta_keywords,
                headings=headings[:10],
                clean_text=clean_text[:50000],
                outbound_links=list(outbound_links),
                language=lang,
                canonical_url=canonical_url,
                favicon_url=favicon_url,
                og_image=og_image,
                content_hash=""
            )
        except Exception:
            return None

    @staticmethod
    def _fallback_extract(html_bytes: bytes, url: str) -> Optional[ExtractedDocument]:
        try:
            text = html_bytes.decode("utf-8", errors="ignore")
            title_m = re.search(r"<title[^>]*>(.*?)</title>", text, re.IGNORECASE | re.DOTALL)
            title = title_m.group(1).strip() if title_m else ""

            desc_m = re.search(r'<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']', text, re.IGNORECASE)
            desc = desc_m.group(1).strip() if desc_m else ""

            clean = re.sub(r"<[^>]+>", " ", text)
            clean = re.sub(r"\s+", " ", clean).strip()

            links = re.findall(r'<a[^>]*href=["\'](.*?)["\']', text, re.IGNORECASE)
            outbound = [urljoin(url, l) for l in links if not l.startswith(("#", "javascript:"))]

            domain = urlparse(url).netloc.lower()
            return ExtractedDocument(
                url=url,
                domain=domain,
                title=title or domain,
                meta_description=desc,
                clean_text=clean[:50000],
                outbound_links=outbound
            )
        except Exception:
            return None
