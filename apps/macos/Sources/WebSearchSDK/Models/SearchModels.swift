import Foundation

/// Supported search categories matching backend routing
public enum SearchCategory: String, Codable, CaseIterable, Identifiable, Sendable {
    case all = "all"
    case images = "images"
    case news = "news"
    case videos = "videos"
    case tech = "tech"
    case code = "code"

    public var id: String { rawValue }

    public var displayName: String {
        switch self {
        case .all: return "All"
        case .images: return "Images"
        case .news: return "News"
        case .videos: return "Videos"
        case .tech: return "Tech"
        case .code: return "Code"
        }
    }

    public var systemImage: String {
        switch self {
        case .all: return "magnifyingglass"
        case .images: return "photo.stack"
        case .news: return "newspaper"
        case .videos: return "play.rectangle"
        case .tech: return "cpu"
        case .code: return "chevron.left.forwardslash.chevron.right"
        }
    }
}

/// SafeSearch enforcement level
public enum SafeSearchLevel: String, Codable, CaseIterable, Identifiable, Sendable {
    case strict = "strict"
    case moderate = "moderate"
    case off = "off"

    public var id: String { rawValue }

    public var displayName: String {
        switch self {
        case .strict: return "Strict"
        case .moderate: return "Moderate"
        case .off: return "Off"
        }
    }
}

/// Time filter options
public enum TimeFilter: String, Codable, CaseIterable, Identifiable, Sendable {
    case all = "all"
    case day = "day"
    case week = "week"
    case month = "month"
    case year = "year"

    public var id: String { rawValue }

    public var displayName: String {
        switch self {
        case .all: return "Any Time"
        case .day: return "Past 24 Hours"
        case .week: return "Past Week"
        case .month: return "Past Month"
        case .year: return "Past Year"
        }
    }
}

/// Request parameters for search queries
public struct SearchQueryParams: Sendable, Equatable {
    public var query: String
    public var category: SearchCategory
    public var page: Int
    public var perPage: Int
    public var language: String?
    public var country: String?
    public var safeSearch: SafeSearchLevel
    public var timeFilter: TimeFilter

    public init(
        query: String,
        category: SearchCategory = .all,
        page: Int = 1,
        perPage: Int = 10,
        language: String? = nil,
        country: String? = nil,
        safeSearch: SafeSearchLevel = .moderate,
        timeFilter: TimeFilter = .all
    ) {
        self.query = query
        self.category = category
        self.page = page
        self.perPage = perPage
        self.language = language
        self.country = country
        self.safeSearch = safeSearch
        self.timeFilter = timeFilter
    }
}

/// Individual organic search result item
public struct SearchResultItem: Codable, Identifiable, Hashable, Sendable {
    public let id: String
    public let url: String
    public let domain: String
    public let title: String
    public let snippet: String
    public let highlightedSnippet: String?
    public let publishedAt: String?
    public let indexedAt: String?
    public let favicon: String?
    public let rankScore: Double?
    public let category: String?
    public let contentType: String?

    public init(
        id: String = UUID().uuidString,
        url: String,
        domain: String,
        title: String,
        snippet: String,
        highlightedSnippet: String? = nil,
        publishedAt: String? = nil,
        indexedAt: String? = nil,
        favicon: String? = nil,
        rankScore: Double? = nil,
        category: String? = nil,
        contentType: String? = nil
    ) {
        self.id = id
        self.url = url
        self.domain = domain
        self.title = title
        self.snippet = snippet
        self.highlightedSnippet = highlightedSnippet
        self.publishedAt = publishedAt
        self.indexedAt = indexedAt
        self.favicon = favicon
        self.rankScore = rankScore
        self.category = category
        self.contentType = contentType
    }
}

/// Instant answer card structure (calculator, time, network, cheatsheets)
public struct InstantAnswer: Codable, Hashable, Sendable {
    public let type: String
    public let title: String
    public let subtitle: String?
    public let content: String
    public let sourceUrl: String?
    public let sourceName: String?

    public init(
        type: String,
        title: String,
        subtitle: String? = nil,
        content: String,
        sourceUrl: String? = nil,
        sourceName: String? = nil
    ) {
        self.type = type
        self.title = title
        self.subtitle = subtitle
        self.content = content
        self.sourceUrl = sourceUrl
        self.sourceName = sourceName
    }
}

/// Image Search result item
public struct ImageSearchResult: Codable, Identifiable, Hashable, Sendable {
    public let id: String
    public let imageUrl: String
    public let originalUrl: String?
    public let thumbnailUrl: String?
    public let pageUrl: String?
    public let domain: String?
    public let alt: String?
    public let title: String?
    public let width: Int?
    public let height: Int?
    public let aspectRatio: Double?
    public let mimeType: String?

    public init(
        id: String = UUID().uuidString,
        imageUrl: String,
        originalUrl: String? = nil,
        thumbnailUrl: String? = nil,
        pageUrl: String? = nil,
        domain: String? = nil,
        alt: String? = nil,
        title: String? = nil,
        width: Int? = nil,
        height: Int? = nil,
        aspectRatio: Double? = nil,
        mimeType: String? = nil
    ) {
        self.id = id
        self.imageUrl = imageUrl
        self.originalUrl = originalUrl
        self.thumbnailUrl = thumbnailUrl
        self.pageUrl = pageUrl
        self.domain = domain
        self.alt = alt
        self.title = title
        self.width = width
        self.height = height
        self.aspectRatio = aspectRatio
        self.mimeType = mimeType
    }
}

/// Top level search response payload
public struct SearchResponse: Codable, Sendable {
    public let query: String
    public let totalHits: Int
    public let page: Int
    public let perPage: Int
    public let totalPages: Int
    public let executionTimeMs: Double
    public let instantAnswer: InstantAnswer?
    public let results: [SearchResultItem]
    public let imageResults: [ImageSearchResult]?
    public let suggestions: [String]?
    public let correctedQuery: String?

    public init(
        query: String,
        totalHits: Int,
        page: Int,
        perPage: Int,
        totalPages: Int,
        executionTimeMs: Double,
        instantAnswer: InstantAnswer? = nil,
        results: [SearchResultItem] = [],
        imageResults: [ImageSearchResult]? = nil,
        suggestions: [String]? = nil,
        correctedQuery: String? = nil
    ) {
        self.query = query
        self.totalHits = totalHits
        self.page = page
        self.perPage = perPage
        self.totalPages = totalPages
        self.executionTimeMs = executionTimeMs
        self.instantAnswer = instantAnswer
        self.results = results
        self.imageResults = imageResults
        self.suggestions = suggestions
        self.correctedQuery = correctedQuery
    }
}

/// Query autocomplete suggestion response
public struct SuggestionResponse: Codable, Sendable {
    public let query: String
    public let suggestions: [String]

    public init(query: String, suggestions: [String]) {
        self.query = query
        self.suggestions = suggestions
    }
}

/// Random word response
public struct RandomWordResponse: Codable, Sendable {
    public let word: String
    public let url: String?

    public init(word: String, url: String? = nil) {
        self.word = word
        self.url = url
    }
}
