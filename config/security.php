<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Security Firewall Master Switch
    |--------------------------------------------------------------------------
    |
    | When set to false, all security firewall middleware checks, rule engine
    | inspections, and rate limiters will be bypassed completely.
    |
    */

    'enabled' => env('SECURITY_ENABLED', env('SHIELD_ENABLED', true)),

    /*
    |--------------------------------------------------------------------------
    | Zero-Configuration Automatic Middleware & Migrations
    |--------------------------------------------------------------------------
    |
    | When set to true, the package automatically attaches firewall and upload
    | security middleware to web/api groups and auto-loads database migrations
    | with zero manual setup required.
    |
    */

    'auto_apply_middleware' => env('SECURITY_AUTO_MIDDLEWARE', false),
    'auto_load_migrations' => env('SECURITY_AUTO_MIGRATIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Security Mode
    |--------------------------------------------------------------------------
    |
    | Operating modes for the security firewall engine:
    |
    | 'monitor'  - Inspects and logs detected threats without blocking requests.
    | 'balanced' - Blocks high-confidence threats, throttles suspicious requests.
    | 'strict'   - Aggressive blocking policy for high-security environments.
    |
    */

    'mode' => env('SECURITY_MODE', env('SHIELD_MODE', 'balanced')),

    /*
    |--------------------------------------------------------------------------
    | Fail-Safe Mode
    |--------------------------------------------------------------------------
    |
    | Determines system behavior when backend dependencies (Redis, Database)
    | experience runtime exceptions or network connectivity failures:
    |
    | 'fail_open'   - Logs system errors and allows traffic through safely.
    | 'fail_closed' - Aborts suspicious requests with 500/403 on internal error.
    |
    */

    'failure_mode' => env('SECURITY_FAILURE_MODE', 'fail_open'),

    /*
    |--------------------------------------------------------------------------
    | Web Application Protection
    |--------------------------------------------------------------------------
    */

    'web' => [
        'enabled' => true,
        'inspect_query' => true,
        'inspect_body' => true,
        'inspect_headers' => true,
        'inspect_cookies' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Protection
    |--------------------------------------------------------------------------
    */

    'api' => [
        'enabled' => true,
        'rate_limit' => (int) env('SECURITY_API_RATE_LIMIT', 120),
        'burst' => (int) env('SECURITY_API_BURST', 30),
        'auth_failure_threshold' => 10,
        'auth_failure_decay' => 900, // 15 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule Engine Detectors
    |--------------------------------------------------------------------------
    */

    'firewall' => [
        'sql_injection' => true,
        'xss' => true,
        'path_traversal' => true,
        'command_injection' => true,
        'ssrf' => true,
        'scanner_detection' => true,
        'bot_detection' => true,
        'parameter_pollution' => true,
        'encoding_attacks' => true,
        'request_size' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum Allowed Request Payload Size (in Bytes)
    |--------------------------------------------------------------------------
    |
    | Default is 10MB (10 * 1024 * 1024).
    |
    */

    'max_request_size' => 10485760,

    /*
    |--------------------------------------------------------------------------
    | Risk Scoring Thresholds
    |--------------------------------------------------------------------------
    |
    | Risk scores range from 0 to 100 based on threat evaluation:
    |   0 - 29: LOW (Allow)
    |  30 - 59: MEDIUM (Log / Throttle)
    |  60 - 79: HIGH (Challenge / Throttle)
    |  80 - 100: CRITICAL (Block)
    |
    */

    'risk_thresholds' => [
        'low' => 30,
        'medium' => 60,
        'high' => 80,
    ],

    /*
    |--------------------------------------------------------------------------
    | SSRF Protection Configuration
    |--------------------------------------------------------------------------
    */

    'ssrf' => [
        'enabled' => true,
        'allowlist' => [
            // 'api.example.com',
        ],
        'block_private_ips' => true,
        'block_metadata_ips' => true, // 169.254.169.254 etc.
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security Settings
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'enabled' => true,

        // Maximum upload file size in KB (Default: 20MB)
        'max_size' => 20480,

        // Safe allowed file extensions
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip',
        ],

        // Validate MIME type matching header vs actual inspection
        'validate_mime' => true,

        // Validate magic byte signatures
        'validate_signature' => true,

        // Move flagged files to non-public quarantine storage
        'quarantine' => true,
        'quarantine_path' => 'security/quarantine',

        // Reject script & executable file uploads automatically
        'reject_executable' => true,

        // Archive inspection settings (ZIP/TAR)
        'archive_scan' => true,
        'max_archive_files' => 1000,
        'max_archive_size' => 104857600, // 100MB decompressed size limit
        'max_decompression_ratio' => 100, // Zip bomb protection threshold ratio
    ],

    /*
    |--------------------------------------------------------------------------
    | Bot & Scanner Detection Configuration
    |--------------------------------------------------------------------------
    */

    'bot' => [
        'enabled' => true,
        'behavior_detection' => true,
        'block_known_scanners' => true,
        'trusted_bots' => [
            'googlebot',
            'bingbot',
            'duckduckbot',
            'slurp',
            'baiduspider',
            'facebookexternalhit',
            'twitterbot',
            'linkedinbot',
        ],
        'blocked_bots' => [
            'sqlmap',
            'nikto',
            'nmap',
            'gobuster',
            'dirbuster',
            'acunetix',
            'nessus',
            'masscan',
            'zgrab',
            'w3af',
            'netsparker',
            'openvas',
            'havij',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting & Application DoS Mitigation
    |--------------------------------------------------------------------------
    */

    'ddos' => [
        'enabled' => true,
        'algorithm' => 'sliding_window', // 'token_bucket' or 'sliding_window'
        'requests_per_minute' => (int) env('SECURITY_RPM', 120),
        'burst' => (int) env('SECURITY_BURST', 30),
        'temporary_block' => true,
        'block_duration' => 3600, // 1 hour temporary block
        'redis_prefix' => env('SHIELD_REDIS_PREFIX', 'security'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Access Control Rules
    |--------------------------------------------------------------------------
    */

    'ip' => [
        'allowlist' => [
            // '127.0.0.1',
            // '192.168.1.0/24',
        ],
        'blocklist' => [
            // '10.0.0.99',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies & Client IP Resolution
    |--------------------------------------------------------------------------
    */

    'trusted_proxies' => [
        'headers' => [
            'X-Forwarded-For',
            'CF-Connecting-IP', // Cloudflare
            'X-Real-IP',
        ],
        'trust_all' => false,
        'proxies' => [
            // '127.0.0.1',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic Route Obfuscation Settings
    |--------------------------------------------------------------------------
    */

    'route_obfuscation' => [
        'enabled' => (bool) env('SECURITY_ROUTE_OBFUSCATION', false),
        'prefix' => 'r',
        'routes' => [
            // 'admin.users',
            // 'admin.dashboard',
        ],
        'exclude' => [
            'login',
            'logout',
            'password.*',
            'api.*',
            'webhooks.*',
            'sanctum.*',
        ],
        'api' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Response Security Headers
    |--------------------------------------------------------------------------
    */

    'headers' => [
        'enabled' => true,
        'hsts' => true,
        'hsts_max_age' => 31536000,
        'hsts_include_subdomains' => true,
        'content_type_options' => true, // nosniff
        'frame_options' => 'SAMEORIGIN', // DENY or SAMEORIGIN
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
        'csp' => [
            'mode' => 'disabled', // 'disabled', 'report-only', 'enabled'
            'policy' => "default-src 'self'",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Auditing Settings
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => true,
        'database' => true,
        'channel' => env('SECURITY_LOG_CHANNEL', 'daily'),
        'retention_days' => 30,
        'log_payloads' => (bool) env('SECURITY_LOG_PAYLOADS', false),
        'redact_fields' => [
            'password',
            'password_confirmation',
            'secret',
            'api_key',
            'token',
            'access_token',
            'refresh_token',
            'authorization',
            'cookie',
            'credit_card',
            'card_number',
            'cvv',
            'ssn',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Dashboard
    |--------------------------------------------------------------------------
    */

    'dashboard' => [
        'enabled' => true,
        'path' => 'security',
        'require_auth' => (bool) env('SECURITY_DASHBOARD_AUTH', false),
        'middleware' => ['web'],
    ],

];
