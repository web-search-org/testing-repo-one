// swift-tools-version: 6.0
import PackageDescription

let package = Package(
    name: "WebSearchMac",
    platforms: [
        .macOS(.v14)
    ],
    products: [
        .library(
            name: "WebSearchSDK",
            targets: ["WebSearchSDK"]
        ),
        .executable(
            name: "WebSearchApp",
            targets: ["WebSearchApp"]
        )
    ],
    dependencies: [],
    targets: [
        .target(
            name: "WebSearchSDK",
            dependencies: [],
            path: "Sources/WebSearchSDK"
        ),
        .executableTarget(
            name: "WebSearchApp",
            dependencies: ["WebSearchSDK"],
            path: "Sources/WebSearchApp"
        ),
        .testTarget(
            name: "WebSearchSDKTests",
            dependencies: ["WebSearchSDK"],
            path: "Tests/WebSearchSDKTests"
        ),
        .testTarget(
            name: "WebSearchAppTests",
            dependencies: ["WebSearchApp", "WebSearchSDK"],
            path: "Tests/WebSearchAppTests"
        )
    ]
)
