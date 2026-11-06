<?php

namespace RobTrehy\LaravelApplicationSettings;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobProcessing;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ApplicationSettingsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-application-settings')
            ->hasConfigFile('application-settings')
            ->hasMigration('create_settings_table');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('application-settings', fn () => new ApplicationSettings);

        $this->app->alias('application-settings', ApplicationSettings::class);
    }

    public function packageBooted(): void
    {
        $this->app->make(Dispatcher::class)->listen([
            JobProcessing::class,
            // Register optional Octane events by name without requiring Octane.
            'Laravel\Octane\Events\RequestReceived',
            'Laravel\Octane\Events\TaskReceived',
            'Laravel\Octane\Events\TickReceived',
        ], static function (): void {
            ApplicationSettings::refresh();
        });
    }
}
