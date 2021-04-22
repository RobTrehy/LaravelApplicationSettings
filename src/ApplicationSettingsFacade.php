<?php

namespace RobTrehy\LaravelApplicationSettings;

use Illuminate\Support\Facades\Facade;

class ApplicationSettingsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'application-settings';
    }
}
