import Foundation

/// URL Inspection Result structure matching Search Console API
public struct UrlInspectionResult: Codable, Identifiable, Sendable {
    public var id: String { url }
    public let pageId: String?
    public let url: String
    public let domain: String
    public let isIndexed: Bool
    public let indexStatus: String
    public let verdict: String
    public let verdictDescription: String
    public let coverage: CoverageInfo
    public let enhancements: EnhancementsInfo
    public let metadata: InspectionMetadata?
    public let inboundLinks: [InboundLink]?

    public init(
        pageId: String? = nil,
        url: String,
        domain: String,
        isIndexed: Bool,
        indexStatus: String,
        verdict: String,
        verdictDescription: String,
        coverage: CoverageInfo,
        enhancements: EnhancementsInfo,
        metadata: InspectionMetadata? = nil,
        inboundLinks: [InboundLink]? = nil
    ) {
        self.pageId = pageId
        self.url = url
        self.domain = domain
        self.isIndexed = isIndexed
        self.indexStatus = indexStatus
        self.verdict = verdict
        self.verdictDescription = verdictDescription
        self.coverage = coverage
        self.enhancements = enhancements
        self.metadata = metadata
        self.inboundLinks = inboundLinks
    }

    enum CodingKeys: String, CodingKey {
        case pageId = "id"
        case url
        case domain
        case isIndexed
        case indexStatus
        case verdict
        case verdictDescription
        case coverage
        case enhancements
        case metadata
        case inboundLinks
    }
}

public struct CoverageInfo: Codable, Sendable {
    public let discovery: String
    public let crawlTime: String?
    public let crawledAs: String
    public let crawlAllowed: String
    public let pageFetch: String
    public let indexingAllowed: String
    public let userCanonical: String?
    public let engineCanonical: String?

    public init(
        discovery: String,
        crawlTime: String? = nil,
        crawledAs: String,
        crawlAllowed: String,
        pageFetch: String,
        indexingAllowed: String,
        userCanonical: String? = nil,
        engineCanonical: String? = nil
    ) {
        self.discovery = discovery
        self.crawlTime = crawlTime
        self.crawledAs = crawledAs
        self.crawlAllowed = crawlAllowed
        self.pageFetch = pageFetch
        self.indexingAllowed = indexingAllowed
        self.userCanonical = userCanonical
        self.engineCanonical = engineCanonical
    }
}

public struct EnhancementsInfo: Codable, Sendable {
    public let mobileFriendly: Bool
    public let https: Bool
    public let pageRank: Double?
    public let inLinksCount: Int?
    public let outLinksCount: Int?
    public let wordCount: Int?

    public init(
        mobileFriendly: Bool,
        https: Bool,
        pageRank: Double? = nil,
        inLinksCount: Int? = nil,
        outLinksCount: Int? = nil,
        wordCount: Int? = nil
    ) {
        self.mobileFriendly = mobileFriendly
        self.https = https
        self.pageRank = pageRank
        self.inLinksCount = inLinksCount
        self.outLinksCount = outLinksCount
        self.wordCount = wordCount
    }
}

public struct InspectionMetadata: Codable, Sendable {
    public let title: String?
    public let description: String?
    public let keywords: [String]?
    public let headings: [String]?
    public let ogImage: String?
    public let favicon: String?
    public let category: String?
    public let language: String?

    public init(
        title: String? = nil,
        description: String? = nil,
        keywords: [String]? = nil,
        headings: [String]? = nil,
        ogImage: String? = nil,
        favicon: String? = nil,
        category: String? = nil,
        language: String? = nil
    ) {
        self.title = title
        self.description = description
        self.keywords = keywords
        self.headings = headings
        self.ogImage = ogImage
        self.favicon = favicon
        self.category = category
        self.language = language
    }
}

public struct InboundLink: Codable, Identifiable, Hashable, Sendable {
    public var id: String { sourceUrl + targetUrlOrEmpty }
    public let sourceUrl: String
    public let sourceDomain: String
    public let targetUrlOrEmpty: String
    public let anchorText: String?
    public let isExternal: Bool

    public init(
        sourceUrl: String,
        sourceDomain: String,
        targetUrlOrEmpty: String = "",
        anchorText: String? = nil,
        isExternal: Bool = true
    ) {
        self.sourceUrl = sourceUrl
        self.sourceDomain = sourceDomain
        self.targetUrlOrEmpty = targetUrlOrEmpty
        self.anchorText = anchorText
        self.isExternal = isExternal
    }

    enum CodingKeys: String, CodingKey {
        case sourceUrl = "source_url"
        case sourceDomain = "source_domain"
        case targetUrlOrEmpty = "target_url"
        case anchorText = "anchor_text"
        case isExternal = "is_external"
    }

    public init(from decoder: Decoder) throws {
        let container = try decoder.container(keyedBy: CodingKeys.self)
        self.sourceUrl = try container.decode(String.self, forKey: .sourceUrl)
        self.sourceDomain = try container.decode(String.self, forKey: .sourceDomain)
        self.targetUrlOrEmpty = (try? container.decode(String.self, forKey: .targetUrlOrEmpty)) ?? ""
        self.anchorText = try? container.decode(String.self, forKey: .anchorText)
        self.isExternal = (try? container.decode(Bool.self, forKey: .isExternal)) ?? true
    }
}

public struct RequestIndexingResponse: Codable, Sendable {
    public let success: Bool
    public let message: String
    public let jobId: String?
    public let url: String?

    public init(success: Bool, message: String, jobId: String? = nil, url: String? = nil) {
        self.success = success
        self.message = message
        self.jobId = jobId
        self.url = url
    }
}

public struct PerformanceSummary: Codable, Sendable {
    public let totalClicks: Int
    public let totalImpressions: Int
    public let averageCtr: Double
    public let averagePosition: Double

    public init(totalClicks: Int, totalImpressions: Int, averageCtr: Double, averagePosition: Double) {
        self.totalClicks = totalClicks
        self.totalImpressions = totalImpressions
        self.averageCtr = averageCtr
        self.averagePosition = averagePosition
    }
}

public struct PerformanceQueryItem: Codable, Identifiable, Sendable {
    public var id: String { query }
    public let query: String
    public let clicks: Int
    public let impressions: Int
    public let ctr: Double
    public let position: Double

    public init(query: String, clicks: Int, impressions: Int, ctr: Double, position: Double) {
        self.query = query
        self.clicks = clicks
        self.impressions = impressions
        self.ctr = ctr
        self.position = position
    }
}

public struct PerformancePageItem: Codable, Identifiable, Sendable {
    public var id: String { url }
    public let url: String
    public let title: String
    public let clicks: Int
    public let impressions: Int
    public let ctr: Double
    public let position: Double

    public init(url: String, title: String, clicks: Int, impressions: Int, ctr: Double, position: Double) {
        self.url = url
        self.title = title
        self.clicks = clicks
        self.impressions = impressions
        self.ctr = ctr
        self.position = position
    }
}

public struct PerformanceMetrics: Codable, Sendable {
    public let domain: String
    public let period: String
    public let summary: PerformanceSummary
    public let queries: [PerformanceQueryItem]
    public let pages: [PerformancePageItem]

    public init(
        domain: String,
        period: String,
        summary: PerformanceSummary,
        queries: [PerformanceQueryItem] = [],
        pages: [PerformancePageItem] = []
    ) {
        self.domain = domain
        self.period = period
        self.summary = summary
        self.queries = queries
        self.pages = pages
    }
}

public struct LinksSummary: Codable, Sendable {
    public let totalExternalLinks: Int
    public let totalLinkingDomains: Int
    public let totalInternalLinks: Int

    public init(totalExternalLinks: Int, totalLinkingDomains: Int, totalInternalLinks: Int) {
        self.totalExternalLinks = totalExternalLinks
        self.totalLinkingDomains = totalLinkingDomains
        self.totalInternalLinks = totalInternalLinks
    }
}

public struct LinkingDomainItem: Codable, Identifiable, Sendable {
    public var id: String { domain }
    public let domain: String
    public let linkCount: Int
    public let targetPagesCount: Int

    public init(domain: String, linkCount: Int, targetPagesCount: Int) {
        self.domain = domain
        self.linkCount = linkCount
        self.targetPagesCount = targetPagesCount
    }
}

public struct LinkedPageItem: Codable, Identifiable, Sendable {
    public var id: String { url }
    public let url: String
    public let incomingLinks: Int
    public let linkingDomainsCount: Int

    public init(url: String, incomingLinks: Int, linkingDomainsCount: Int) {
        self.url = url
        self.incomingLinks = incomingLinks
        self.linkingDomainsCount = linkingDomainsCount
    }
}

public struct AnchorTextItem: Codable, Identifiable, Sendable {
    public var id: String { text }
    public let text: String
    public let count: Int

    public init(text: String, count: Int) {
        self.text = text
        self.count = count
    }
}

public struct InternalPageItem: Codable, Identifiable, Sendable {
    public var id: String { url }
    public let url: String
    public let internalLinks: Int

    public init(url: String, internalLinks: Int) {
        self.url = url
        self.internalLinks = internalLinks
    }
}

public struct RecentLinkItem: Codable, Identifiable, Sendable {
    public let id: String
    public let sourceUrl: String
    public let sourceDomain: String
    public let targetUrl: String
    public let targetDomain: String
    public let anchorText: String?
    public let isExternal: Bool
    public let rel: String?
    public let createdAt: String

    enum CodingKeys: String, CodingKey {
        case id
        case sourceUrl = "source_url"
        case sourceDomain = "source_domain"
        case targetUrl = "target_url"
        case targetDomain = "target_domain"
        case anchorText = "anchor_text"
        case isExternal = "is_external"
        case rel
        case createdAt = "created_at"
    }
}

public struct LinksReport: Codable, Sendable {
    public let domain: String
    public let summary: LinksSummary
    public let topLinkingDomains: [LinkingDomainItem]
    public let topLinkedPages: [LinkedPageItem]
    public let topAnchorTexts: [AnchorTextItem]
    public let topInternalPages: [InternalPageItem]
    public let recentLinks: [RecentLinkItem]

    public init(
        domain: String,
        summary: LinksSummary,
        topLinkingDomains: [LinkingDomainItem] = [],
        topLinkedPages: [LinkedPageItem] = [],
        topAnchorTexts: [AnchorTextItem] = [],
        topInternalPages: [InternalPageItem] = [],
        recentLinks: [RecentLinkItem] = []
    ) {
        self.domain = domain
        self.summary = summary
        self.topLinkingDomains = topLinkingDomains
        self.topLinkedPages = topLinkedPages
        self.topAnchorTexts = topAnchorTexts
        self.topInternalPages = topInternalPages
        self.recentLinks = recentLinks
    }
}

public struct VerifyDomainResponse: Codable, Sendable {
    public let verified: Bool
    public let message: String

    public init(verified: Bool, message: String) {
        self.verified = verified
        self.message = message
    }
}
