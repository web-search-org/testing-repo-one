/**
 * Web-Search.org - Shared Types and Protocol Definitions
 */

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
  id: string | number;
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
  id: string;
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
  id: string;
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
