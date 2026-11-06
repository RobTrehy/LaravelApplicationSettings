<?php

namespace RobTrehy\LaravelApplicationSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see ApplicationSettings
 */
class ApplicationSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RobTrehy\LaravelApplicationSettings\ApplicationSettings::class;
    }
}
