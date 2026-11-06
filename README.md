# Laravel Application Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/robtrehy/laravel-application-settings.svg?style=flat-square)](https://packagist.org/packages/robtrehy/laravel-application-settings)
[![GitHub Tests Action Status](https://github.com/robtrehy/LaravelApplicationSettings/actions/workflows/run-tests.yml/badge.svg)](https://github.com/robtrehy/LaravelApplicationSettings/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/robtrehy/LaravelApplicationSettings/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/robtrehy/LaravelApplicationSettings/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/robtrehy/laravel-application-settings.svg?style=flat-square)](https://packagist.org/packages/robtrehy/laravel-application-settings)

This is a package for Laravel that can be used to store and access settings for your application.
The settings are stored in a single database table. The default configuration stores this in a `settings` table.

## Installation

You can install the package via composer:

```bash
composer require robtrehy/laravel-application-settings
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="application-settings-config"
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="application-settings-migrations"
php artisan migrate
```

This will add the table defined in your configuration file to your database.
    
## Configuration
Open `config/application-settings.php` to adjust the packages configuration. 

If this file doesn't exist, run 
`php artisan vendor:publish --tag="application-settings-config"` 
to create the default configuration file.

Set `table`, `key`, and `value` to match your requirements.

Laravel Application Settings uses your application's configured Laravel cache store to reduce the number of queries on your database. To disable shared caching, you can use the `null` driver.
The cache key supplied by Laravel Application Settings can be set by changing the `cache.key` configuration value.


#### Example configuration
```php
return [
    'database' => [
        'table' => 'settings',
        'key' => 'key',
        'value' => 'value',
    ],
    'cache' => [
        'key' => 'application-settings',
    ],
];
```

## Usage

Import the settings class in the PHP file where you use these examples:

```php
use RobTrehy\LaravelApplicationSettings\ApplicationSettings;
```

### Set a Setting

```php
ApplicationSettings::set('site_name', 'My application');
```

The setting is saved to the database immediately.

### Get a Setting

Pass a second argument to supply a default for a missing setting. Without a default, a missing setting returns `null`.

```php
$siteName = ApplicationSettings::get('site_name', 'Default application');
```

### Get multiple Settings

Supply an array of keys and an optional array of defaults keyed by setting name. Missing settings without a matching default return `null`.

```php
$settings = ApplicationSettings::get(
    ['site_name', 'support_email'],
    ['site_name' => 'Default application', 'support_email' => 'support@example.com'],
);
```

### Get all Setting

Get all application settings as an array keyed by setting name:

```php
$settings = ApplicationSettings::all();
```

### Check if an Application Setting is set
```php
$hasSiteName = ApplicationSettings::has('site_name');
```

Returns `true` if the key exists, otherwise `false`.

### Save a Setting
All settings are saved automatically when `ApplicationSettings::set();` is called.

### Delete a Setting
```php
ApplicationSettings::delete('site_name');
```
There will be no return from this call.

### Stored value types

The published migration stores values in a non-nullable `text` column. Use strings for predictable round trips. The package does not automatically encode arrays or objects as JSON, or cast stored values back to booleans, integers, or other PHP types.

Encode and decode structured values explicitly:

```php
ApplicationSettings::set('navigation', json_encode(['home', 'contact'], JSON_THROW_ON_ERROR));

$navigation = json_decode(
    ApplicationSettings::get('navigation', '[]'),
    true,
    flags: JSON_THROW_ON_ERROR,
);
```

### Refresh settings in long-running processes

The package discards its in-process settings before each queue job and each Octane request, task, or tick. The next access reloads settings from Laravel's cache, or the database on a cache miss. Repeated reads within the same operation still use the in-process settings.

In custom long-running commands, call this between operations:

```php
ApplicationSettings::refresh();
```

This method clears only the in-process settings and reloads lazily on the next access. It does not invalidate the shared cache. Changes made through `set()` and `delete()` continue to invalidate the shared cache automatically. If you change the database directly, also forget the configured cache key.

### Database transactions

Inside a transaction on the settings database connection, reads bypass the shared cache and reload from the database on each access. This allows reads to see changes within the transaction without caching uncommitted values. Transactional snapshots are discarded after the transaction ends.

Writes and deletions invalidate the shared cache immediately and again after the outermost transaction commits, so an older value cached by another process before commit is invalidated. Rolled-back changes are not published to the shared cache.

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing
Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities
Please review our [security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## License
This Laravel package is free software distributed under the terms of the MIT license.
See [LICENSE](LICENSE)

