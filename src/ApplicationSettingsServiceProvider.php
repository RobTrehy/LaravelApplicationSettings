<?php

namespace RobTrehy\LaravelApplicationSettings;

use Illuminate\Support\ServiceProvider;

class ApplicationSettingsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Export the config
            $this->publishes([
                __DIR__ . '/config/application-settings.php' => config_path('application-settings.php'),
            ], 'config');

            // Export the migration
            if (! class_exists('CreateSettingsTable')) {
                $this->publishes([
                    __DIR__ . '/database/migrations/create_settings_table.php.stub'
                    => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_settings_table.php'),
                ], 'migrations');
            }
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ApplicationSettings::class, function () {
            return new ApplicationSettings();
        });

        $this->app->alias(ApplicationSettings::class, 'application-settings');

        $this->mergeConfigFrom(__DIR__.'/config/application-settings.php', 'application-settings');
    }
}
