<?php

namespace RobTrehy\LaravelApplicationSettings;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class ApplicationSettings
 *
 * This class handles the settings for the application.
 */
class ApplicationSettings
{
    /**
     * The cache of the Applications Settings
     *
     * @var object|null
     */
    protected static $settings;

    protected static $hasLoaded;

    /**
     * ApplicationSettings Constructor
     */
    public function __construct() {}

    /**
     * ApplicationSettings destructor
     */
    public function __destruct() {}

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
        $keyColumn = config('application-settings.database.key');
        $valueColumn = config('application-settings.database.value');

        $settings = Cache::rememberForever(config('application-settings.cache.key'), function () use ($keyColumn, $valueColumn) {
            return DB::table(config('application-settings.database.table'))
                ->select([$keyColumn, $valueColumn])
                ->get();
        });

        $data = [];
        foreach ($settings as $setting) {
            $data[$setting->{$keyColumn}] = $setting->{$valueColumn};
        }

        self::$settings = collect($data);
        self::$hasLoaded = true;
    }

    /**
     * Get a settings by key
     *
     * This function will return the setting by its key.
     * If a value does not exist, the supplied default value will be returned
     *
     * @param  string|array  $default  (optional)
     * @return mixed
     */
    public static function get(string|array $key, string|array|null $default = null)
    {
        self::settingsLoaded();

        if (is_array($key)) {
            $defaults = is_array($default) ? $default : [];

            foreach ($key as $_key) {
                if (self::$settings->has($_key)) {
                    $settings[$_key] = self::$settings->get($_key);
                } else {
                    $settings[$_key] = array_key_exists($_key, $defaults)
                        ? $defaults[$_key] : null;
                }
            }

            return $settings ?? '';
        }

        if (self::$settings->has($key)) {
            return self::$settings->get($key);
        } else {
            return $default;
        }
    }

    /**
     * Set the setting by key
     *
     * @param  mixed  $value
     */
    public static function set(string $key, $value)
    {
        self::settingsLoaded();
        self::$settings->put($key, $value);

        try {
            self::save($key);
        } catch (Throwable $exception) {
            self::refresh();

            throw $exception;
        }
    }

    /**
     * Returns true if setting exists
     *
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
     * Discard in-process settings so the next access reloads from the shared cache.
     */
    public static function refresh(): void
    {
        self::$settings = null;
        self::$hasLoaded = false;
    }

    /**
     * Deletes a setting
     */
    public static function delete(string $key)
    {
        try {
            DB::table(config('application-settings.database.table'))
                ->where(config('application-settings.database.key'), $key)
                ->delete();

            self::resetCache();
        } catch (Throwable $exception) {
            self::refresh();

            throw $exception;
        }
    }

    /**
     * Save a setting, or all settings when no key is supplied
     */
    protected static function save(?string $key = null)
    {
        $settings = $key === null ? self::$settings : [$key => self::$settings->get($key)];

        foreach ($settings as $key => $value) {
            $exist = DB::table(config('application-settings.database.table'))
                ->where(config('application-settings.database.key'), $key)
                ->exists();
            if ($exist) {
                DB::table(config('application-settings.database.table'))
                    ->where(config('application-settings.database.key'), $key)
                    ->update([config('application-settings.database.value') => $value]);
            } else {
                DB::table(config('application-settings.database.table'))
                    ->insert([
                        config('application-settings.database.key') => $key,
                        config('application-settings.database.value') => $value,
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
        Cache::forget(config('application-settings.cache.key'));
        self::$hasLoaded = false;
    }
}
