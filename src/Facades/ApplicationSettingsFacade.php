<?php

namespace RobTrehy\LaravelApplicationSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \RobTrehy\LaravelApplicationSettings\ApplicationSettings
 */
class LaravelApplicationSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \RobTrehy\LaravelApplicationSettings\ApplicationSettings::class;
    }
}
