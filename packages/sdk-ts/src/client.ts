import type {
  SearchQuery,
  SearchResponse,
  SuggestionResponse,
  CrawlJob,
  CrawlJobRequest,
  EngineStats,
  UrlInspectionResult,
  PerformanceMetrics,
} from '@web-search/shared-types';

export interface SubmitSiteRequest {
  url: string;
  category?: 'all' | 'tech' | 'code' | 'news' | 'science' | 'general';
  max_pages?: number;
  is_sitemap?: boolean;
}

export interface SubmitSiteResponse {
  success: boolean;
  message: string;
  jobId: string;
  domain: string;
  url: string;
  status: string;
}

export interface WebSearchClientOptions {
  baseUrl?: string;
  apiKey?: string;
  timeoutMs?: number;
  customHeaders?: Record<string, string>;
}

export class WebSearchClient {
  private baseUrl: string;
  private apiKey?: string;
  private timeoutMs: number;
  private customHeaders: Record<string, string>;

  constructor(options: WebSearchClientOptions = {}) {
    this.baseUrl = (options.baseUrl || 'https://web-search.org').replace(/\/$/, '');
    this.apiKey = options.apiKey;
    this.timeoutMs = options.timeoutMs ?? 10000;
    this.customHeaders = options.customHeaders ?? {};
  }

  private async request<T>(path: string, options: RequestInit = {}): Promise<T> {
    const url = `${this.baseUrl}${path}`;
    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'User-Agent': 'WebSearch-SDK-TS/0.1.0',
      ...this.customHeaders,
      ...((options.headers as Record<string, string>) || {}),
    };

    if (this.apiKey) {
      headers['Authorization'] = `Bearer ${this.apiKey}`;
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.timeoutMs);

    try {
      const response = await fetch(url, {
        ...options,
        headers,
        signal: controller.signal,
      });

      if (!response.ok) {
        let errorMessage = `HTTP Error ${response.status}: ${response.statusText}`;
        try {
          const body = await response.json();
          if (body?.message) {
            errorMessage = body.message;
          }
        } catch {
          // ignore json parse error on non-json error responses
        }
        throw new Error(errorMessage);
      }

      return (await response.json()) as T;
    } finally {
      clearTimeout(timeoutId);
    }
  }

  /**
   * Perform a search query
   */
  public async search(params: SearchQuery | string): Promise<SearchResponse> {
    const queryParams = typeof params === 'string' ? { q: params } : params;
    const searchParams = new URLSearchParams();

    for (const [key, value] of Object.entries(queryParams)) {
      if (value !== undefined && value !== null) {
        searchParams.append(key, String(value));
      }
    }

    return this.request<SearchResponse>(`/api/v1/search?${searchParams.toString()}`);
  }

  /**
   * Get search query autocomplete suggestions
   */
  public async suggest(query: string): Promise<SuggestionResponse> {
    const searchParams = new URLSearchParams({ q: query });
    return this.request<SuggestionResponse>(`/api/v1/suggest?${searchParams.toString()}`);
  }

  /**
   * Submit a new website or sitemap to be indexed
   */
  public async submitWebsite(request: SubmitSiteRequest): Promise<SubmitSiteResponse> {
    return this.request<SubmitSiteResponse>('/api/v1/submit', {
      method: 'POST',
      body: JSON.stringify(request),
    });
  }

  /**
   * Submit a new URL / domain for crawling
   */
  public async submitCrawl(job: CrawlJobRequest): Promise<CrawlJob> {
    return this.request<CrawlJob>('/api/v1/crawl', {
      method: 'POST',
      body: JSON.stringify(job),
    });
  }

  /**
   * Get the status of an ongoing or completed crawl job
   */
  public async getCrawlStatus(jobId: string): Promise<CrawlJob> {
    return this.request<CrawlJob>(`/api/v1/crawl/status/${encodeURIComponent(jobId)}`);
  }

  /**
   * Get search engine statistics and system health
   */
  public async getStats(): Promise<EngineStats> {
    return this.request<EngineStats>('/api/v1/stats');
  }

  /**
   * Search Console: Inspect a URL in the search index
   */
  public async inspectUrl(url: string): Promise<UrlInspectionResult> {
    return this.request<UrlInspectionResult>(`/api/v1/console/inspect?url=${encodeURIComponent(url)}`);
  }

  /**
   * Search Console: Request priority crawl & re-indexing
   */
  public async requestIndexing(url: string): Promise<{ success: boolean; message: string; jobId: string }> {
    return this.request('/api/v1/console/request-indexing', {
      method: 'POST',
      body: JSON.stringify({ url }),
    });
  }

  /**
   * Search Console: Retrieve performance metrics for a domain
   */
  public async getPerformance(domain: string, period: string = '28d'): Promise<PerformanceMetrics> {
    return this.request<PerformanceMetrics>(`/api/v1/console/performance?domain=${encodeURIComponent(domain)}&period=${encodeURIComponent(period)}`);
  }

  /**
   * Search Console: Verify domain ownership
   */
  public async verifyDomain(domain: string, method: 'dns_txt' | 'meta_tag' = 'meta_tag'): Promise<{ verified: boolean; message: string }> {
    return this.request('/api/v1/console/verify-domain', {
      method: 'POST',
      body: JSON.stringify({ domain, method }),
    });
  }
}
