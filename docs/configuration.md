# Package Configuration Guide

The package configuration is stored in `config/security.php` (with `config/shield.php` fallback).

## Environment Variables

| Variable | Default | Description |
| :--- | :--- | :--- |
| `SECURITY_ENABLED` | `true` | Master toggle to enable or disable firewall protection |
| `SECURITY_MODE` | `balanced` | Operating mode (`monitor`, `balanced`, `strict`) |
| `SECURITY_FAILURE_MODE` | `fail_open` | Fallback behavior on internal error (`fail_open` or `fail_closed`) |
| `SECURITY_RPM` | `120` | Default rate limit (requests per minute) |
| `SECURITY_BURST` | `30` | Burst request limit |
| `SECURITY_MALWARE_SCANNER` | `clamav` | Anti-malware scanner driver (`clamav` or `null`) |
| `SECURITY_ROUTE_OBFUSCATION` | `false` | Enable or disable route obfuscation |

## Security Modes

* **`monitor`**: Analyzes requests and logs threats without blocking traffic (ideal for testing in production).
* **`balanced`**: Default mode. Blocks high-confidence attacks and throttles suspicious repeat offenders.
* **`strict`**: Lower thresholds for aggressive security environments.
