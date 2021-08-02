<?php

namespace RobTrehy\LaravelApplicationSettings;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class ApplicationSettings
 *
 * This class handles the settings for the application.
 *
 * @package RobTrehy\LaravelApplicationSettings
 */
class ApplicationSettings
{
    /**
     * The cache of the Applications Settings
     *
     * @var object
     */
    protected static $settings;

    protected static $hasLoaded;

    /**
     * ApplicationSettings Constructor
     */
    public function __construct()
    {
    }

    /**
     * ApplicationSettings destructor
     */
    public function __destruct()
    {
    }

    /**
     * Check for settings, load if not
     */
    protected static function settingsLoaded()
    {
        if (self::$hasLoaded) {
            return;
        }
        self::getSettings();
    }

    /**
     * Get all settings from the database
     */
    protected static function getSettings()
    {
        self::$hasLoaded = true;

        $settings = Cache::rememberForever(config('application-settings.cache.key'), function () {
            return DB::table(config('application-settings.database.table'))
                    ->select([
                        config('application-settings.database.key'),
                        config('application-settings.database.value')
                    ])
                    ->get();
        });

        $data = [];
        foreach ($settings as $setting) {
            $data[$setting->{config('application-settings.database.key')}]
                = $setting->{config('application-settings.database.value')};
        }

        self::$settings = collect($data);
    }

    /**
     * Get a settings by key
     *
     * This function will return the setting by its key.
     * If a value does not exist, the supplied default value will be returned
     *
     * @param string $key
     * @param string $default (optional)
     * @return mixed
     */
    public static function get(string $key, string $default = null)
    {
        self::settingsLoaded();

        if (self::has($key)) {
            return self::$settings->get($key);
        } else {
            return $default;
        }
    }

    /**
     * Set the setting by key
     *
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value)
    {
        self::settingsLoaded();
        self::$settings->put($key, $value);
        self::save();
    }

    /**
     * Returns true if setting exists
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key)
    {
        self::settingsLoaded();
        return self::$settings->has($key);
    }

    /**
     * Returns all settings as an array
     *
     * @return object
     */
    public static function all()
    {
        self::settingsLoaded();
        return self::$settings->toArray();
    }

    /**
     * Deletes a setting
     */
    public static function delete(string $key)
    {
        self::settingsLoaded();
        if (self::has($key)) {
            self::$settings->pull($key);
            DB::table(config('application-settings.database.table'))
                ->where(config('application-settings.database.key'), $key)
                ->delete();
        }
    }

    /**
     * Save all settings to database
     */
    protected static function save()
    {
        foreach (self::$settings as $key => $value) {
            $exist = DB::table(config('application-settings.database.table'))
                ->select([config('application-settings.database.key')])
                ->where(config('application-settings.database.key'), $key)
                ->count();
            if ($exist) {
                DB::table(config('application-settings.database.table'))
                    ->where(config('application-settings.database.key'), $key)
                    ->update([config('application-settings.database.value') => $value]);
            } else {
                DB::table(config('application-settings.database.table'))
                    ->insert([
                        config('application-settings.database.key') => $key,
                        config('application-settings.database.value') => $value
                    ]);
            }
        }

        self::resetCache();
    }

    /**
     * Clear cached data
     */
    protected static function resetCache()
    {
        Cache::forget(config('user-preferences.cache.prefix') . Auth::id() . config('user-preferences.cache.suffix'));
    }
}
