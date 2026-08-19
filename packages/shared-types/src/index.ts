/**
 * Web-Search.org - Shared Types and Protocol Definitions
 */

export type UUID = string;

export type SearchCategory = 'all' | 'images' | 'news' | 'videos' | 'tech' | 'code';
export type SafeSearchLevel = 'strict' | 'moderate' | 'off';
export type TimeFilter = 'all' | 'day' | 'week' | 'month' | 'year';

export interface SearchQuery {
  q: string;
  category?: SearchCategory;
  page?: number;
  perPage?: number;
  lang?: string;
  country?: string;
  safeSearch?: SafeSearchLevel;
  timeFilter?: TimeFilter;
  site?: string;
  filetype?: string;
}

export interface SearchResultItem {
  id: UUID;
  url: string;
  domain: string;
  title: string;
  snippet: string;
  highlightedSnippet?: string;
  publishedAt?: string;
  indexedAt: string;
  favicon?: string;
  rankScore?: number;
  category?: SearchCategory;
  contentType?: string;
  metadata?: Record<string, unknown>;
}

export interface InstantAnswer {
  type: 'calculator' | 'weather' | 'definition' | 'conversion' | 'code' | 'knowledge_graph' | 'cheatsheet';
  title: string;
  subtitle?: string;
  content: string;
  sourceUrl?: string;
  sourceName?: string;
  data?: Record<string, unknown>;
}

export interface SearchResponse {
  query: string;
  totalHits: number;
  page: number;
  perPage: number;
  totalPages: number;
  executionTimeMs: number;
  instantAnswer?: InstantAnswer | null;
  results: SearchResultItem[];
  suggestions?: string[];
  correctedQuery?: string | null;
}

export interface SuggestionResponse {
  query: string;
  suggestions: string[];
}

export type CrawlJobStatus = 'queued' | 'running' | 'completed' | 'failed' | 'paused';

export interface CrawlJobRequest {
  url: string;
  maxDepth?: number;
  maxPages?: number;
  allowSubdomains?: boolean;
  respectRobots?: boolean;
  rateLimitMs?: number;
  priority?: number;
  customUserAgent?: string;
}

export interface CrawlJob {
  id: UUID;
  seedUrl: string;
  status: CrawlJobStatus;
  pagesCrawled: number;
  pagesDiscovered: number;
  pagesIndexed: number;
  errorsCount: number;
  startedAt?: string;
  finishedAt?: string;
  createdAt: string;
  updatedAt: string;
  progressPercent: number;
  error?: string;
}

export interface ExtractedPage {
  url: string;
  canonicalUrl?: string;
  domain: string;
  title: string;
  metaDescription?: string;
  metaKeywords?: string[];
  textContent: string;
  rawHtml?: string;
  outLinks: string[];
  inLinksCount?: number;
  language?: string;
  contentType: string;
  contentHash: string;
  httpStatus: number;
  responseTimeMs: number;
  crawledAt: string;
}

export interface IndexerDocument {
  id: UUID;
  url: string;
  domain: string;
  title: string;
  body: string;
  description: string;
  keywords: string[];
  language: string;
  pageRank: number;
  bm25Tokens: Record<string, number>;
  crawledAt: string;
  lastIndexedAt: string;
}

export interface EngineStats {
  totalDocuments: number;
  totalDomains: number;
  totalCrawledPages: number;
  activeCrawlJobs: number;
  averageQueryTimeMs: number;
  queriesLast24h: number;
  systemHealth: 'healthy' | 'degraded' | 'unhealthy';
  uptimeSeconds: number;
}

/**
 * Web-Search Console Type Definitions
 */
export interface UrlInspectionResult {
  id?: UUID;
  url: string;
  domain: string;
  isIndexed: boolean;
  indexStatus: string;
  verdict: string;
  verdictDescription: string;
  coverage: {
    discovery: string;
    crawlTime: string | null;
    crawledAs: string;
    crawlAllowed: string;
    pageFetch: string;
    indexingAllowed: string;
    userCanonical?: string;
    engineCanonical?: string;
  };
  enhancements: {
    mobileFriendly: boolean;
    https: boolean;
    pageRank?: number;
    inLinksCount?: number;
    outLinksCount?: number;
    wordCount?: number;
  };
  metadata?: {
    title: string;
    description: string;
    keywords: string[];
    headings: string[];
    ogImage?: string;
    favicon?: string;
    category?: string;
    language?: string;
  } | null;
  inboundLinks?: Array<{
    source_url: string;
    source_domain: string;
    anchor_text?: string;
    is_external: boolean;
  }>;
}

export interface PerformanceMetrics {
  domain: string;
  period: string;
  summary: {
    totalClicks: number;
    totalImpressions: number;
    averageCtr: number;
    averagePosition: number;
  };
  queries: Array<{
    query: string;
    clicks: number;
    impressions: number;
    ctr: number;
    position: number;
  }>;
  pages: Array<{
    url: string;
    title: string;
    clicks: number;
    impressions: number;
    ctr: number;
    position: number;
  }>;
}

export interface LinksReport {
  domain: string;
  summary: {
    totalExternalLinks: number;
    totalLinkingDomains: number;
    totalInternalLinks: number;
  };
  topLinkingDomains: Array<{
    domain: string;
    linkCount: number;
    targetPagesCount: number;
  }>;
  topLinkedPages: Array<{
    url: string;
    incomingLinks: number;
    linkingDomainsCount: number;
  }>;
  topAnchorTexts: Array<{
    text: string;
    count: number;
  }>;
  topInternalPages: Array<{
    url: string;
    internalLinks: number;
  }>;
  recentLinks: Array<{
    id: UUID;
    source_url: string;
    source_domain: string;
    target_url: string;
    target_domain: string;
    anchor_text?: string;
    is_external: boolean;
    rel?: string;
    created_at: string;
  }>;
}

export interface SitemapItem {
  id: UUID;
  domainId: UUID;
  url: string;
  status: 'submitted' | 'processing' | 'success' | 'error';
  totalUrls: number;
  indexedUrls: number;
  lastCrawledAt?: string;
}
