---
name: lumen-artisan-segfault
description: Troubleshoot segmentation fault (exit 139) when running php artisan commands in Lumen 5.1 with PHP 7.3
source: auto-skill
extracted_at: '2026-06-08T07:52:10.399Z'
---

# Troubleshooting Artisan Segfault in Lumen

## Problem

Running `php artisan` commands (e.g., `config:cache`, `qrcode:generate`, `--version`) causes **segmentation fault** (exit code 139) with no output. This occurs in Lumen 5.1.7 running PHP 7.3.33.

## Root Cause

The segfault is caused by interactions between:
- PHP 7.3.33 (old, EOL since 2021)
- Lumen's service providers (Datatables, GeoIP, Queue)
- Symfony Console component used by Artisan CLI

The application bootstrap works fine when loaded via `php -r`, but crashes when executed through the `artisan` entry point.

## Diagnostic Approach

Use systematic isolation to identify the failing component:

1. **Test basic bootstrap** - Verify if the issue is in bootstrap or CLI execution:
   ```bash
   php -r "require 'vendor/autoload.php'; \$app = new Laravel\Lumen\Application(realpath('.')); echo 'OK';"
   ```

2. **Add components incrementally** - Test each bootstrap step:
   ```bash
   php -r "
   require 'vendor/autoload.php';
   Dotenv::load('.');
   \$app = new Laravel\Lumen\Application(realpath('.'));
   \$app->withFacades();
   \$app->withEloquent();
   \$app->configure('geoip');
   // ... add providers one by one
   echo 'OK';
   "
   ```

3. **Test service providers individually** - Register each provider separately to find the culprit:
   - AppServiceProvider
   - DatatablesServiceProvider
   - GeoIPServiceProvider
   - QueueServiceProvider

4. **Compare CLI vs inline execution** - If bootstrap works in `php -r` but not `php artisan`, the issue is in the Artisan CLI layer, not the application code.

## Solution

When Artisan CLI is broken but bootstrap works:

### Option 1: Use Standalone Scripts (Recommended)

Create standalone PHP scripts that bootstrap Lumen manually and execute the required logic:

```php
#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';
Dotenv::load(__DIR__);

$app = new Laravel\Lumen\Application(realpath(__DIR__));
$app->withFacades();
$app->withEloquent();
$app->configure('geoip');

// Configure database manually for CLI
$app->singleton('config', function () {
    return new Illuminate\Config\Repository([
        'database' => [
            'default' => env('DB_CONNECTION', 'sqlsrv'),
            'connections' => [
                'sqlsrv' => [
                    'driver'   => 'sqlsrv',
                    'host'     => env('DB_HOST'),
                    'port'     => env('DB_PORT'),
                    'database' => env('DB_DATABASE'),
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                ],
            ],
        ],
    ]);
});

// Your logic here
```

### Option 2: Use Existing Working Scripts

This project has working alternatives:
- `php generate-qr.php <short_url>` - Single QR generation
- `php bulk-generate-qrcode [--limit=N] [--force]` - Bulk QR generation

These scripts work because they:
- Don't use Lumen's Artisan kernel
- Bootstrap the application manually
- Use BaconQrCode directly instead of QrCode facade

## Key Indicators

| Symptom | Likely Cause |
|---------|--------------|
| Exit 139 with no output | Segfault in PHP extension or native code |
| Bootstrap works in `php -r` but not `php artisan` | Artisan CLI layer issue |
| Individual providers work, combined they crash | Provider interaction bug |
| Old PHP version (< 7.4) | Known bugs in older PHP releases |

## Prevention

- Upgrade PHP to 8.0+ if possible
- Avoid Artisan CLI commands in production with Lumen
- Use standalone scripts for CLI operations
- Consider Lumen's design philosophy (minimal, no config cache needed)
