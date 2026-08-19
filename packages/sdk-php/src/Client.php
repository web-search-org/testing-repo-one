<?php

namespace WebSearch\Sdk;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use WebSearch\Sdk\Exceptions\WebSearchException;

class Client
{
    protected GuzzleClient $http;
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct(
        string $baseUrl = 'https://web-search.org',
        ?string $apiKey = null,
        array $guzzleOptions = []
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;

        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => 'WebSearch-SDK-PHP/0.1.0',
        ];

        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $this->http = new GuzzleClient(array_merge([
            'base_uri' => $this->baseUrl,
            'timeout' => 10.0,
            'headers' => $headers,
        ], $guzzleOptions));
    }

    /**
     * Perform a search query
     */
    public function search(string|array $query): array
    {
        $params = is_string($query) ? ['q' => $query] : $query;
        return $this->request('GET', '/api/v1/search', ['query' => $params]);
    }

    /**
     * Autocomplete suggestions
     */
    public function suggest(string $query): array
    {
        return $this->request('GET', '/api/v1/suggest', ['query' => ['q' => $query]]);
    }

    /**
     * Submit a website or sitemap for indexing
     */
    public function submitWebsite(array $data): array
    {
        return $this->request('POST', '/api/v1/submit', ['json' => $data]);
    }

    /**
     * Submit a URL to be crawled
     */
    public function submitCrawl(array $jobData): array
    {
        return $this->request('POST', '/api/v1/crawl', ['json' => $jobData]);
    }

    /**
     * Get crawl job status
     */
    public function getCrawlStatus(string $jobId): array
    {
        return $this->request('GET', '/api/v1/crawl/status/' . urlencode($jobId));
    }

    /**
     * Search Console: Inspect a URL
     */
    public function inspectUrl(string $url): array
    {
        return $this->request('GET', '/api/v1/console/inspect', ['query' => ['url' => $url]]);
    }

    /**
     * Search Console: Request priority indexing
     */
    public function requestIndexing(string $url): array
    {
        return $this->request('POST', '/api/v1/console/request-indexing', ['json' => ['url' => $url]]);
    }

    /**
     * Search Console: Get domain performance
     */
    public function getPerformance(string $domain, string $period = '28d'): array
    {
        return $this->request('GET', '/api/v1/console/performance', ['query' => ['domain' => $domain, 'period' => $period]]);
    }

    /**
     * Search Console: Get links & interlinking report
     */
    public function getLinksReport(string $domain): array
    {
        return $this->request('GET', '/api/v1/console/links', ['query' => ['domain' => $domain]]);
    }

    /**
     * Get engine statistics
     */
    public function getStats(): array
    {
        return $this->request('GET', '/api/v1/stats');
    }

    protected function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->http->request($method, $uri, $options);
            $body = (string) $response->getBody();
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            throw new WebSearchException('WebSearch API HTTP error: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (\JsonException $e) {
            throw new WebSearchException('Invalid JSON received from WebSearch API: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
