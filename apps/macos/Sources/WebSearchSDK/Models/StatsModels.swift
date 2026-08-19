import Foundation

/// Health state of the search engine cluster
public enum SystemHealth: String, Codable, Sendable {
    case healthy = "healthy"
    case degraded = "degraded"
    case unhealthy = "unhealthy"

    public var displayName: String {
        switch self {
        case .healthy: return "Healthy"
        case .degraded: return "Degraded"
        case .unhealthy: return "Unhealthy"
        }
    }

    public var systemImage: String {
        switch self {
        case .healthy: return "checkmark.circle.fill"
        case .degraded: return "exclamationmark.triangle.fill"
        case .unhealthy: return "xmark.octagon.fill"
        }
    }
}

/// Real-time search engine cluster metrics
public struct EngineStats: Codable, Sendable {
    public let totalDocuments: Int
    public let totalDomains: Int
    public let totalCrawledPages: Int
    public let activeCrawlJobs: Int
    public let averageQueryTimeMs: Double
    public let queriesLast24h: Int?
    public let systemHealth: SystemHealth
    public let uptimeSeconds: Int?

    public init(
        totalDocuments: Int,
        totalDomains: Int,
        totalCrawledPages: Int,
        activeCrawlJobs: Int,
        averageQueryTimeMs: Double,
        queriesLast24h: Int? = 0,
        systemHealth: SystemHealth = .healthy,
        uptimeSeconds: Int? = 0
    ) {
        self.totalDocuments = totalDocuments
        self.totalDomains = totalDomains
        self.totalCrawledPages = totalCrawledPages
        self.activeCrawlJobs = activeCrawlJobs
        self.averageQueryTimeMs = averageQueryTimeMs
        self.queriesLast24h = queriesLast24h
        self.systemHealth = systemHealth
        self.uptimeSeconds = uptimeSeconds
    }

    enum CodingKeys: String, CodingKey {
        case totalDocuments
        case totalDomains
        case totalCrawledPages
        case activeCrawlJobs
        case averageQueryTimeMs
        case queriesLast24h
        case systemHealth
        case uptimeSeconds
    }

    public init(from decoder: Decoder) throws {
        let container = try decoder.container(keyedBy: CodingKeys.self)
        self.totalDocuments = try container.decode(Int.self, forKey: .totalDocuments)
        self.totalDomains = try container.decode(Int.self, forKey: .totalDomains)
        self.totalCrawledPages = (try? container.decode(Int.self, forKey: .totalCrawledPages)) ?? 0
        self.activeCrawlJobs = (try? container.decode(Int.self, forKey: .activeCrawlJobs)) ?? 0
        self.averageQueryTimeMs = (try? container.decode(Double.self, forKey: .averageQueryTimeMs)) ?? 0.0
        self.queriesLast24h = try? container.decode(Int.self, forKey: .queriesLast24h)
        
        let healthStr = (try? container.decode(String.self, forKey: .systemHealth)) ?? "healthy"
        self.systemHealth = SystemHealth(rawValue: healthStr) ?? .healthy
        
        self.uptimeSeconds = try? container.decode(Int.self, forKey: .uptimeSeconds)
    }
}
