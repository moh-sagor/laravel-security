# sagor/laravel-security

<p align="center">
  <strong style="font-size: 1.5rem; color: #00f3ff;">DEVELOPED BY MOH SAGOR</strong><br>
  <em>Defense-in-Depth Application Security Firewall & Cyber Desk Workstation for Laravel</em>
</p>

<p align="center">
  <a href="https://packagist.org/packages/sagor/laravel-security"><img src="https://img.shields.io/packagist/v/sagor/laravel-security.svg" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/sagor/laravel-security"><img src="https://img.shields.io/packagist/dt/sagor/laravel-security.svg" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/sagor/laravel-security"><img src="https://img.shields.io/packagist/l/sagor/laravel-security.svg" alt="License"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/php-7.2%20--%208.4-blue.svg" alt="PHP Version"></a>
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/laravel-5.5%20--%2013.x-red.svg" alt="Laravel Version"></a>
</p>

---

## ⚡ Overview

**`sagor/laravel-security`** is a production-ready, high-performance defense-in-depth security package built for **Laravel 5.5 through 13.x** on **PHP 7.2 through 8.4**. 

Created by **Moh Sagor**, it protects web endpoints, REST APIs, and file upload forms against OWASP Top 10 vulnerabilities including SQL Injection, Cross-Site Scripting (XSS), Path Traversal, Remote Command Execution, SSRF, Malicious File Uploads, Rate Abuse, and Automated Security Scanners.

It comes equipped with an interactive **Cyber Command Center Dashboard** and **Cyber Desk All Attempts Workstation** featuring live 24-hour database time-series charts, holographic payload inspector modals, sub-millisecond threat classification, and interactive Cyberpunk animations.

---

## 📋 Table of Contents

- [Key Features](#-key-features)
- [Requirements & Compatibility](#-requirements--compatibility)
- [Installation & Setup](#-installation--setup)
- [Middleware Configuration](#-middleware-configuration)
- [Cyber Desk Workstation & Dashboard](#-cyber-desk-workstation--dashboard)
- [Security Engines & Threat Rules](#-security-engines--threat-rules)
- [File Upload Protection & Quarantine](#-file-upload-protection--quarantine)
- [Configuration Reference (`config/security.php`)](#-configuration-reference-configsecurityphp)
- [Artisan CLI Commands](#-artisan-cli-commands)
- [Creating Custom Security Rules](#-creating-custom-security-rules)
- [License & Author Credits](#-license--author-credits)

---

## ✨ Key Features

- 🛡️ **Advanced Risk Scoring Engine**: Evaluates request payloads, headers, parameters, and user-agents through confidence scoring (`SecurityEngine`, `SecurityContext`, `RiskScore`, `SecurityDecision`) to eliminate false positives.
- 💻 **Cyber Desk All Attempts Workstation (`/security/attempts`)**: Dedicated futuristic audit desk for searching, filtering, and inspecting all intrusion attempt events.
- 🔍 **Holographic Payload Inspector**: Base64-decoded modal inspector displaying request UUIDs, risk scores, exact threat vectors, target endpoints, IP hashes, and redacted payload snapshots.
- 📈 **Real 24-Hour Time-Series Telemetry**: Canvas chart fed directly from your MySQL database showing real hourly attack trends and block metrics.
- 📁 **Malicious File Upload Shield**: Multi-stage upload validation featuring magic byte binary signature verification, ZIP bomb / archive decompression ratio checks, safe filename sanitization, and automatic non-public storage quarantine (`storage/app/security/quarantine/`).
- 🤖 **Bot & Scanner Detection**: Blocks malicious automated scanners (`sqlmap`, `nikto`, `gobuster`, `nmap`, `dirbuster`) while allowing verified search engine crawlers (Googlebot, Bingbot).
- ⏱️ **Sliding Window Rate Limiter**: High-speed token bucket rate limiting backed by Redis or Laravel Cache.
- 🔒 **Cryptographic Route Obfuscation**: Dynamically masks sensitive application routes (e.g., `/admin/users` -> `/r/X9k21LmP`) without breaking URL generation or authentication callbacks.
- 🎵 **Cyber Audio Synthesizer**: Subtle Web Audio API laser beep feedback with mute/unmute toggle.
- ⚡ **Zero External Frontend Dependencies**: Built entirely with pure vanilla Blade HTML5, CSS3, and JavaScript canvas — no Node/npm build steps required.

---

## ⚙️ Requirements & Compatibility

| Component | Supported Versions |
| :--- | :--- |
| **PHP** | `^7.2`, `^7.3`, `^7.4`, `^8.0`, `^8.1`, `^8.2`, `^8.3`, `^8.4` |
| **Laravel** | `5.5.x` through `13.x` |
| **Database** | MySQL, PostgreSQL, SQLite, MariaDB |
| **Cache Driver** | Redis, Memcached, Array, File, Database |

---

## 🚀 Installation & Setup

### 1. Require the Package

Install via Composer:

```bash
composer require sagor/laravel-security
```

### 2. Run the Installer Command

Publish configuration, migrations, views, and initialize security storage directories:

```bash
php artisan security:install
```

*(You can also use `php artisan shield:install`)*

### 3. Run Database Migrations

Execute migrations to create the required security event tables (`shield_security_events`, `shield_blocked_ips`, `shield_route_maps`, `shield_malware_scans`):

```bash
php artisan migrate
```

---

## 🛡️ Middleware Configuration

### Laravel 11, 12, and 13 (`bootstrap/app.php`)

In modern Laravel applications, register the middleware aliases or append to middleware groups in `bootstrap/app.php`:

```php
use Sagor\LaravelSecurity\Http\Middleware\SecurityMiddleware;
use Sagor\LaravelSecurity\Http\Middleware\SecurityUploadMiddleware;
use Sagor\LaravelSecurity\Http\Middleware\SecurityApiMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global web protection
        $middleware->web(append: [
            SecurityMiddleware::class,
        ]);

        // Middleware Aliases
        $middleware->alias([
            'security' => SecurityMiddleware::class,
            'security.upload' => SecurityUploadMiddleware::class,
            'security.api' => SecurityApiMiddleware::class,
        ]);
    })->create();
```

### Laravel 5.5 through 10 (`app/Http/Kernel.php`)

Add the middleware to `$routeMiddleware` or `$middlewareGroups` in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \Sagor\LaravelSecurity\Http\Middleware\SecurityMiddleware::class,
        \Sagor\LaravelSecurity\Http\Middleware\SecurityUploadMiddleware::class,
    ],
];

protected $routeMiddleware = [
    'security' => \Sagor\LaravelSecurity\Http\Middleware\SecurityMiddleware::class,
    'security.upload' => \Sagor\LaravelSecurity\Http\Middleware\SecurityUploadMiddleware::class,
    'security.api' => \Sagor\LaravelSecurity\Http\Middleware\SecurityApiMiddleware::class,
];
```

### Protecting Web & File Upload Routes (`routes/web.php`)

```php
Route::middleware(['security', 'security.upload'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::post('/upload', [UploadController::class, 'store']);
});
```

---

## 🖥️ Cyber Desk Workstation & Dashboard

Access the built-in security workstation in your web browser:

- **Cyber Command Center Overview**: `http://localhost:8000/security`
- **Cyber Desk All Attempts Workstation**: `http://localhost:8000/security/attempts`

### Features of the Cyber Desk:
- **Search & Filter Console**: Filter attempts by keyword, IP hash, route URI, threat vector (SQLi, XSS, Path Traversal, Bot Scan, Upload Threat, Rate Limit), severity, action, and pagination limits.
- **Hologram Inspector**: Click `[ 🔍 INSPECT ATTEMPT ]` on any record to open the decoded base64 hologram modal overlay showing the raw payload snapshot, risk score, and request headers.
- **Live Local Time**: Features a live ticking client clock (`toLocaleTimeString()`) synchronized with your system.
- **Matrix Rain & Laser Sweep**: Fully animated matrix particle background and holographic scanner line.

---

## 🔍 Security Engines & Threat Rules

The package ships with 10 built-in security detection rules:

| Rule Identifier | Threat Vector | Description |
| :--- | :--- | :--- |
| `sqli.detector` | SQL Injection | Detects `UNION SELECT`, stacked queries, blind sleep functions, boolean conditions |
| `xss.detector` | Cross-Site Scripting | Identifies `<script>`, inline event handlers (`onload=`, `onerror=`), `javascript:` URIs |
| `path_traversal.detector` | Path Traversal | Blocks `../`, `..\\`, `/etc/passwd`, Windows system file references |
| `command_injection.detector` | Command Injection | Intercepts shell metacharacters (`\|`, `;`, `$(...)`, `nc`, `wget`, `curl`, `bash`) |
| `ssrf.detector` | SSRF Attack | Blocks access to cloud metadata IPs (`169.254.169.254`), internal loopback (`127.0.0.1`) |
| `scanner.detector` | Scanner Detection | Identifies security tools (`sqlmap`, `nikto`, `gobuster`, `dirbuster`, `nmap`) |
| `user_agent.detector` | Suspicious User-Agent | Rejects empty, anomalous, or malicious User-Agent headers |
| `request_size.detector` | Request Size | Enforces maximum HTTP body payload boundaries |
| `hpp.detector` | HTTP Parameter Pollution | Detects duplicate key parameter pollution attacks |
| `encoding.detector` | Double/Null Encoding | Intercepts `%00` null bytes and double URL encoding bypasses |

---

## 📁 File Upload Protection & Quarantine

`SecurityUploadMiddleware` intercepts incoming file uploads and executes a 4-step security inspection:

1. **Magic Byte Signature Check**: Compares binary header signatures (e.g. `FF D8 FF` for JPEG, `89 50 4E 47` for PNG, `25 50 44 46` for PDF) against the user-submitted file extension to block executable files disguised with fake extensions.
2. **Archive Bomb Check**: Analyzes compressed archives (`.zip`) to prevent decompression bomb attacks exceeding safety expansion ratios (e.g. 100:1 ratio).
3. **Filename Sanitization**: Strip dangerous extensions, double extensions (`image.png.php`), control characters, and null bytes.
4. **Quarantine Storage**: Automatically moves rejected files to non-public quarantine storage at `storage/app/security/quarantine/` with execution-blocking `.htaccess` controls and logs entries to both `shield_security_events` and `shield_malware_scans`.

---

## ⚙️ Configuration Reference (`config/security.php`)

Publish the configuration file using `php artisan security:install`:

```php
return [
    'enabled' => env('SECURITY_ENABLED', true),
    
    // Operating Mode: 'monitor' (log only), 'balanced' (block high/critical), or 'strict' (block medium+)
    'mode' => env('SECURITY_MODE', 'balanced'),

    'dashboard' => [
        'path' => 'security',
        'middleware' => ['web'],
    ],

    'firewall' => [
        'sql_injection' => true,
        'xss' => true,
        'path_traversal' => true,
        'command_injection' => true,
        'ssrf' => true,
        'scanner_detection' => true,
        'bot_detection' => true,
    ],

    'uploads' => [
        'max_size' => 20480, // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'zip'],
        'validate_signature' => true,
        'quarantine' => true,
    ],

    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    'route_obfuscation' => [
        'enabled' => false,
        'prefix' => 'r',
        'routes' => ['admin.users'],
        'exclude' => ['login', 'logout', 'api.*'],
    ],
];
```

---

## 🛠️ Artisan CLI Commands

| Command | Description |
| :--- | :--- |
| `php artisan security:install` | Run installer, publish configuration, views, and migrations |
| `php artisan security:status` | Display firewall engine health, active modes, and driver status |
| `php artisan security:scan {path}` | Scan a target file or directory for malware signatures |
| `php artisan security:routes` | Generate and display cryptographic obfuscated route mappings |
| `php artisan security:clear` | Flush rate limit caches and temporary blocked IP records |
| `php artisan security:report` | Generate a comprehensive application security summary report |
| `php artisan security:cleanup` | Purge old security log records beyond configured retention days |
| `php artisan security:test` | Run firewall engine self-test against attack payloads |

---

## 🧩 Creating Custom Security Rules

You can easily extend the firewall by implementing the `SecurityRule` interface:

```php
namespace App\Security\Rules;

use Sagor\LaravelSecurity\Contracts\SecurityRule;
use Sagor\LaravelSecurity\Firewall\SecurityContext;
use Sagor\LaravelSecurity\Firewall\SecurityRuleResult;

class BlockForbiddenKeywords implements SecurityRule
{
    public function getId(): string
    {
        return 'custom.forbidden_keywords';
    }

    public function getDescription(): string
    {
        return 'Blocks requests containing internal restricted payload terms.';
    }

    public function check(SecurityContext $context): SecurityRuleResult
    {
        $payload = json_encode($context->getNormalizedPayload());

        if (str_contains($payload, 'INTERNAL_SECRET_KEY')) {
            return SecurityRuleResult::threat(
                $this->getId(),
                'critical',
                1.0,
                95,
                'Forbidden internal keyword detected in payload.'
            );
        }

        return SecurityRuleResult::clean($this->getId());
    }
}
```

Register your custom rule in your `AppServiceProvider`:

```php
use Sagor\LaravelSecurity\Facades\LaravelSecurity;
use App\Security\Rules\BlockForbiddenKeywords;

public function boot()
{
    LaravelSecurity::addRule(new BlockForbiddenKeywords());
}
```

---

## 📄 License & Credits

- **Author / Developer**: **Moh Sagor**
- **Package**: `sagor/laravel-security`
- **License**: Released under the [MIT License](LICENSE).
