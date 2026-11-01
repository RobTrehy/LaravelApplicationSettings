<?php

namespace RobTrehy\LaravelApplicationSettings\Facades;

use Illuminate\Support\Facades\Facade;
use RobTrehy\LaravelApplicationSettings\ApplicationSettings;

/**
 * @see ApplicationSettings
 */
class LaravelApplicationSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApplicationSettings::class;
    }
}
