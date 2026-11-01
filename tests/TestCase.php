<?php

namespace RobTrehy\LaravelApplicationSettings\Tests;

use Illuminate\Foundation\Application;
use RobTrehy\LaravelApplicationSettings\ApplicationSettingsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Get package providers.
     *
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ApplicationSettingsServiceProvider::class,
        ];
    }

    /**
     * Override application aliases.
     *
     * @param  Application  $app
     * @return array
     */
    protected function overrideApplicationProviders($app)
    {
        return [
            'ApplicationSettings' => 'RobTrehy\LaravelApplicationSettings\ApplicationSettingsFacade',
        ];
    }

    /**
     * Ignore package discovery from.
     *
     * @return array
     */
    public function ignorePackageDiscoveriesFrom()
    {
        return [];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
