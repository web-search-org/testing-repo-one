import Foundation

/// Status of crawler execution job
public enum CrawlJobStatus: String, Codable, CaseIterable, Identifiable, Sendable {
    case queued = "queued"
    case running = "running"
    case completed = "completed"
    case failed = "failed"
    case paused = "paused"

    public var id: String { rawValue }

    public var displayName: String {
        switch self {
        case .queued: return "Queued"
        case .running: return "Crawling"
        case .completed: return "Completed"
        case .failed: return "Failed"
        case .paused: return "Paused"
        }
    }

    public var systemImage: String {
        switch self {
        case .queued: return "hourglass"
        case .running: return "arrow.triangle.2.circlepath"
        case .completed: return "checkmark.circle.fill"
        case .failed: return "exclamationmark.triangle.fill"
        case .paused: return "pause.circle.fill"
        }
    }
}

/// Crawl Job model representation
public struct CrawlJob: Codable, Identifiable, Hashable, Sendable {
    public let id: String
    public let seedUrl: String
    public let status: CrawlJobStatus
    public let pagesCrawled: Int
    public let pagesDiscovered: Int
    public let pagesIndexed: Int
    public let errorsCount: Int
    public let progressPercent: Double
    public let startedAt: String?
    public let finishedAt: String?
    public let createdAt: String
    public let updatedAt: String
    public let error: String?

    public init(
        id: String = UUID().uuidString,
        seedUrl: String,
        status: CrawlJobStatus = .queued,
        pagesCrawled: Int = 0,
        pagesDiscovered: Int = 0,
        pagesIndexed: Int = 0,
        errorsCount: Int = 0,
        progressPercent: Double = 0.0,
        startedAt: String? = nil,
        finishedAt: String? = nil,
        createdAt: String = ISO8601DateFormatter().string(from: Date()),
        updatedAt: String = ISO8601DateFormatter().string(from: Date()),
        error: String? = nil
    ) {
        self.id = id
        self.seedUrl = seedUrl
        self.status = status
        self.pagesCrawled = pagesCrawled
        self.pagesDiscovered = pagesDiscovered
        self.pagesIndexed = pagesIndexed
        self.errorsCount = errorsCount
        self.progressPercent = progressPercent
        self.startedAt = startedAt
        self.finishedAt = finishedAt
        self.createdAt = createdAt
        self.updatedAt = updatedAt
        self.error = error
    }

    enum CodingKeys: String, CodingKey {
        case id
        case seedUrl = "seedUrl"
        case status
        case pagesCrawled = "pagesCrawled"
        case pagesDiscovered = "pagesDiscovered"
        case pagesIndexed = "pagesIndexed"
        case errorsCount = "errorsCount"
        case progressPercent = "progressPercent"
        case startedAt = "startedAt"
        case finishedAt = "finishedAt"
        case createdAt = "createdAt"
        case updatedAt = "updatedAt"
        case error
        // Fallback for snake_case if returned directly from Eloquent model
        case snakeSeedUrl = "seed_url"
        case snakePagesCrawled = "pages_crawled"
        case snakePagesDiscovered = "pages_discovered"
        case snakePagesIndexed = "pages_indexed"
        case snakeErrorsCount = "errors_count"
    }

    public init(from decoder: Decoder) throws {
        let container = try decoder.container(keyedBy: CodingKeys.self)
        self.id = try container.decode(String.self, forKey: .id)
        
        if let url = try? container.decode(String.self, forKey: .seedUrl) {
            self.seedUrl = url
        } else {
            self.seedUrl = try container.decode(String.self, forKey: .snakeSeedUrl)
        }

        let rawStatus = (try? container.decode(String.self, forKey: .status)) ?? "queued"
        self.status = CrawlJobStatus(rawValue: rawStatus) ?? .queued

        self.pagesCrawled = (try? container.decode(Int.self, forKey: .pagesCrawled))
            ?? (try? container.decode(Int.self, forKey: .snakePagesCrawled)) ?? 0
        self.pagesDiscovered = (try? container.decode(Int.self, forKey: .pagesDiscovered))
            ?? (try? container.decode(Int.self, forKey: .snakePagesDiscovered)) ?? 0
        self.pagesIndexed = (try? container.decode(Int.self, forKey: .pagesIndexed))
            ?? (try? container.decode(Int.self, forKey: .snakePagesIndexed)) ?? 0
        self.errorsCount = (try? container.decode(Int.self, forKey: .errorsCount))
            ?? (try? container.decode(Int.self, forKey: .snakeErrorsCount)) ?? 0
        
        self.progressPercent = (try? container.decode(Double.self, forKey: .progressPercent)) ?? 0.0
        self.startedAt = try? container.decode(String.self, forKey: .startedAt)
        self.finishedAt = try? container.decode(String.self, forKey: .finishedAt)
        self.createdAt = (try? container.decode(String.self, forKey: .createdAt)) ?? ""
        self.updatedAt = (try? container.decode(String.self, forKey: .updatedAt)) ?? ""
        self.error = try? container.decode(String.self, forKey: .error)
    }

    public func encode(to encoder: Encoder) throws {
        var container = encoder.container(keyedBy: CodingKeys.self)
        try container.encode(id, forKey: .id)
        try container.encode(seedUrl, forKey: .seedUrl)
        try container.encode(status, forKey: .status)
        try container.encode(pagesCrawled, forKey: .pagesCrawled)
        try container.encode(pagesDiscovered, forKey: .pagesDiscovered)
        try container.encode(pagesIndexed, forKey: .pagesIndexed)
        try container.encode(errorsCount, forKey: .errorsCount)
        try container.encode(progressPercent, forKey: .progressPercent)
        try container.encodeIfPresent(startedAt, forKey: .startedAt)
        try container.encodeIfPresent(finishedAt, forKey: .finishedAt)
        try container.encode(createdAt, forKey: .createdAt)
        try container.encode(updatedAt, forKey: .updatedAt)
        try container.encodeIfPresent(error, forKey: .error)
    }
}

/// Request to create a new crawl job
public struct CrawlJobRequest: Codable, Sendable {
    public let url: String
    public let maxDepth: Int?
    public let maxPages: Int?
    public let concurrency: Int?

    public init(
        url: String,
        maxDepth: Int? = 3,
        maxPages: Int? = 200,
        concurrency: Int? = 5
    ) {
        self.url = url
        self.maxDepth = maxDepth
        self.maxPages = maxPages
        self.concurrency = concurrency
    }
}

/// Public website submission request
public struct SubmitSiteRequest: Codable, Sendable {
    public let url: String
    public let category: String?
    public let maxPages: Int?
    public let isSitemap: Bool?

    public init(
        url: String,
        category: String? = "all",
        maxPages: Int? = 50,
        isSitemap: Bool? = false
    ) {
        self.url = url
        self.category = category
        self.maxPages = maxPages
        self.isSitemap = isSitemap
    }

    enum CodingKeys: String, CodingKey {
        case url
        case category
        case maxPages = "max_pages"
        case isSitemap = "is_sitemap"
    }
}

/// Response returned from site submission
public struct SubmitSiteResponse: Codable, Sendable {
    public let success: Bool
    public let message: String
    public let jobId: String?
    public let domain: String?
    public let url: String?
    public let status: String?

    public init(
        success: Bool,
        message: String,
        jobId: String? = nil,
        domain: String? = nil,
        url: String? = nil,
        status: String? = nil
    ) {
        self.success = success
        self.message = message
        self.jobId = jobId
        self.domain = domain
        self.url = url
        self.status = status
    }
}
