# Laravel Application Settings
![](https://img.shields.io/github/workflow/status/RobTrehy/LaravelApplicationSettings/Unit%20Tests?style=flat-square)
![](https://img.shields.io/github/license/RobTrehy/LaravelApplicationSettings?style=flat-square)
![](https://img.shields.io/github/languages/code-size/RobTrehy/LaravelApplicationSettings?style=flat-square)
![](https://img.shields.io/packagist/v/robtrehy/laravel-application-settings?style=flat-square)
![](https://img.shields.io/packagist/dt/robtrehy/laravel-application-settings?style=flat-square)

This is a package for Laravel that can be used to store and access settings for your application.
The settings are stored in a single database table. The default configuration stores this in a `settings` table.

## Installation
1. Run `composer require robtrehy/laravel-application-settings` to include this in your project.
2. Publish the config file with the following command
    ```
    php artisan vendor:publish --provider="RobTrehy\LaravelApplicationSettings\ApplicationSettingsServiceProvider" --tag="config"
    ```
4. Modify the published configuration file to your requirements. The file is located at `config/application-settings.php`.
5. Add the `settings` table to the database. A migration file is included, just run the following command
    ```
    php artisan vendor:publish --provider="RobTrehy\LaravelApplicationSettings\ApplicationSettingsServiceProvider" --tag="migrations" && php artisan migrate
    ```
    This will add the table defined in your configuration file to your database.
    
## Configuration
Open `config/application-settings.php` to adjust the packages configuration. 

If this file doesn't exist, run 
`php artisan vendor:public --provider="RobTrehy\LaravelApplicationSettings\ApplicationSettingsServiceProvider" --tag="config"` 
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

