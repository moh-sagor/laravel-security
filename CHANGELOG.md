# Changelog

All notable changes to `sagor/laravel-security` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-09-26

### Added
- Core `SecurityEngine` pipeline with `SecurityContext`, `RiskScore`, `SecurityPolicy`, and `SecurityDecision`.
- SQL Injection detector (`SqlInjectionRule`).
- Cross-Site Scripting detector (`XssRule`).
- Path Traversal & LFI detector (`PathTraversalRule`).
- OS Command Injection detector (`CommandInjectionRule`).
- SSRF detector (`SsrfRule`).
- Automated Security Scanner detector (`ScannerDetectionRule`).
- Suspicious User Agent detector (`SuspiciousUserAgentRule`).
- Oversized Request payload protector (`RequestSizeRule`).
- HTTP Parameter Pollution detector (`ParameterPollutionRule`).
- Double-encoding and null-byte detector (`EncodingAttackRule`).
- Multi-stage upload security manager with magic byte file signature validator, filename sanitizer, archive bomb analyzer, and non-public quarantine storage manager.
- ClamAV anti-malware integration scanner driver (`ClamAvScanner`) with `NullScanner` fallback.
- Sliding window and token bucket rate limiter (`RateLimiter`, `ThreatTracker`).
- Bot detector (`BotDetector`, `BehaviorAnalyzer`, `BotScore`).
- Cryptographic route obfuscation engine (`RouteObfuscator`, `RouteResolver`, `RouteMap`).
- Real-time Blade security dashboard at `/security`.
- Artisan CLI tools: `security:install`, `security:status`, `security:scan`, `security:routes`, `security:clear`, `security:report`, `security:cleanup`, `security:test`.
- Database migrations for `shield_security_events`, `shield_blocked_ips`, `shield_route_maps`, and `shield_malware_scans`.
- Support for PHP 7.2 through 8.4 and Laravel 5.5 through 13.x.
