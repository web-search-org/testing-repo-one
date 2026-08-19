import Foundation

/// Errors that can occur during WebSearch API interactions
public enum APIError: LocalizedError, Sendable {
    case invalidURL(String)
    case networkError(String)
    case serverError(statusCode: Int, message: String)
    case decodingError(String)
    case timeout
    case offline
    case custom(String)

    public var errorDescription: String? {
        switch self {
        case .invalidURL(let url):
            return "Invalid URL: \(url)"
        case .networkError(let details):
            return "Network connection error: \(details)"
        case .serverError(let code, let msg):
            return "Server error (\(code)): \(msg)"
        case .decodingError(let details):
            return "Data parsing error: \(details)"
        case .timeout:
            return "Request timed out. Please check if the Web-Search server is running."
        case .offline:
            return "Device is offline or the host is unreachable."
        case .custom(let message):
            return message
        }
    }
}
