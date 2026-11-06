<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RobTrehy\LaravelApplicationSettings\ApplicationSettings;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['settings', 'hasLoaded'] as $property) {
        $reflection = new ReflectionProperty(ApplicationSettings::class, $property);
        $reflection->setValue(null, null);
    }

    Cache::forget(config('application-settings.cache.key'));
});

it('can set and get a setting', function () {
    ApplicationSettings::set('test_key', 'test_value');
    $value = ApplicationSettings::get('test_key');

    expect($value)->toBe('test_value');
});

it('can retry loading settings after a database failure', function () {
    $table = config('application-settings.database.table');
    DB::table($table)->insert(['key' => 'test_key', 'value' => 'test_value']);

    config()->set('application-settings.database.table', 'missing_settings_table');

    try {
        expect(fn () => ApplicationSettings::get('test_key'))->toThrow(QueryException::class);
    } finally {
        config()->set('application-settings.database.table', $table);
    }

    expect(ApplicationSettings::get('test_key'))->toBe('test_value');
});

it('can set and get multiple settings', function () {
    ApplicationSettings::set('test_key', 'test_value');
    $values = ApplicationSettings::get(['test_key', 'test_key2']);

    expect($values)
        ->toHaveCount(2)
        ->toHaveKeys(['test_key', 'test_key2'])
        ->not->toHaveKey('key_not_set');
});

it('can get all settings', function () {
    ApplicationSettings::set('test_key', 'test_value');
    ApplicationSettings::set('test_key2', 'test_value2');
    ApplicationSettings::set('last_key', 'last_value');

    $settings = ApplicationSettings::all();

    expect($settings)
        ->toHaveCount(3)
        ->toHaveKeys(['test_key', 'last_key'])
        ->not->toHaveKey('key_not_set');
});

it('can check if a setting is not set', function () {
    $value = ApplicationSettings::has('key_not_set');

    expect($value)->toBeFalse();
});

it('can check if a setting is set', function () {
    ApplicationSettings::set('key_set', 'some_value');
    $value = ApplicationSettings::has('key_set');

    expect($value)->toBeTrue();
});

it('can delete a setting', function () {
    ApplicationSettings::set('key_set', 'some_value');
    ApplicationSettings::delete('key_set');
    $value = ApplicationSettings::has('key_set');

    expect($value)->toBeFalse();
});

it('persists a setting with two queries regardless of the number of settings', function (int $count, string $key) {
    DB::table('settings')->insert(array_map(
        fn (int $index) => ['key' => 'key_'.$index, 'value' => 'original'],
        range(1, $count),
    ));

    ApplicationSettings::all();
    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        expect(ApplicationSettings::set($key, 'updated'))->toBeNull();
        expect(DB::getQueryLog())->toHaveCount(2);
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }

    expect(DB::table('settings')->where('key', $key)->value('value'))->toBe('updated');
    expect(Cache::has(config('application-settings.cache.key')))->toBeFalse();
    expect(ApplicationSettings::get($key))->toBe('updated');
})->with([1, 100])->with(['key_1', 'new_key']);

it('does not overwrite unrelated settings changed after loading', function () {
    DB::table('settings')->insert([
        ['key' => 'first', 'value' => 'original'],
        ['key' => 'second', 'value' => 'original'],
    ]);

    ApplicationSettings::all();
    DB::table('settings')->where('key', 'second')->update(['value' => 'external']);

    ApplicationSettings::set('first', 'updated');

    expect(ApplicationSettings::get('first'))->toBe('updated');
    expect(ApplicationSettings::get('second'))->toBe('external');
});

it('updates every row matching a duplicate setting key', function () {
    DB::table('settings')->insert([
        ['key' => 'duplicate', 'value' => 'first'],
        ['key' => 'duplicate', 'value' => 'second'],
    ]);

    ApplicationSettings::set('duplicate', 'updated');

    expect(DB::table('settings')->where('key', 'duplicate')->pluck('value')->all())
        ->toBe(['updated', 'updated']);
});
