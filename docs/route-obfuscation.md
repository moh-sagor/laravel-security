# Route Obfuscation Guide

Route Obfuscation replaces sensitive internal route endpoints (such as `/admin/users`) with opaque, cryptographically random aliases (such as `/r/7f9a2b8e1c`).

## Enabling Route Obfuscation

Enable route obfuscation in `config/security.php`:

```php
'route_obfuscation' => [
    'enabled' => true,
    'prefix' => 'r',
    'routes' => [
        'admin.users',
        'admin.settings',
    ],
    'exclude' => [
        'login',
        'logout',
        'password.*',
        'api.*',
    ],
],
```

## CLI Route Generation

To generate route alias mappings:

```bash
php artisan security:routes
```

To force regeneration of mappings:

```bash
php artisan security:routes --regenerate
```
