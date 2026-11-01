<?php

namespace RobTrehy\LaravelApplicationSettings;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelApplicationSettingsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-application-settings')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_settings_table');
    }


    public function packageRegistered(): void
    {
        $this->app->singleton('application-settings', fn() => new ApplicationSettings());

        $this->app->alias('application-settings', ApplicationSettings::class);
    }
}
