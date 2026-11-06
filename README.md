# Laravel Application Settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/robtrehy/laravel-application-settings.svg?style=flat-square)](https://packagist.org/packages/robtrehy/laravel-application-settings)
[![GitHub Tests Action Status](https://github.com/robtrehy/laravel-application-settings/actions/workflows/run-tests.yml/badge.svg)](https://github.com/robtrehy/laravel-application-settings/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/robtrehy/laravel-application-settings/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/robtrehy/laravel-application-settings/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
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

Laravel Application Settings uses the Laravel Cache driver to reduce the number of queries on your database. By default Laravel Caches using the `file` driver. If you wish to disable this, you can use the `null` driver.
The cache key supplied by Laravel Application Settings can be set by changing the `cache.key` configuration value.


#### Example configuration
```PHP
    'database' => [
        'table' => 'settings',
        'key' => 'key',
        'value' => 'value'
    ],
    'cache' => [
        'key' => 'application.settings'
    ]
```

## Usage

### Set a Setting
Use this method to set a setting for the application
```PHP
ApplicationSettings::set(string [setting], [value]);
```
The setting will be immediately saved to the database

### Get a Setting
Use this method to get the value of a setting for the application
Pass a second arguement to return a default value if the setting is not set, defaults to `null`
```PHP
ApplicationSettings::get(string [setting], string [default] = null);
```

### Get multiple Settings
Use this method to get the value of multiple settings for the application
Pass a second arguement to return a default value if the setting is not set, defaults to `null`
```PHP
ApplicationSettings::get(array [setting], array [default] = null);
```

### Get all Setting
Use this method to get all of the applications's settings as an array
```PHP
ApplicationSettings::all()
```

### Check if an Application Setting is set
To check if the application has a specific setting set, you can call
```PHP
ApplicationSettings::has(string [setting]);
```
This will return `true` if a value was found, `false` if not.

### Save a Setting
All settings are saved automatically when `ApplicationSettings::set();` is called.

### Delete a Setting
To delete a setting, you can call
```PHP
ApplicationSettings::delete(string [setting]);
```
There will be no return from this call.

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing
Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities
Please review our [security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## License
This Laravel package is free software distributed under the terms of the MIT license.
See [LICENSE](LICENSE)

