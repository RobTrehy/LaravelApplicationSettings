<?php

use Illuminate\Contracts\Queue\Job;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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

it('discards unsaved values after a failed insert or update', function (string $key) {
    DB::table('settings')->insert(['key' => 'existing', 'value' => 'persisted']);
    ApplicationSettings::all();

    expect(fn () => ApplicationSettings::set($key, null))->toThrow(QueryException::class);

    expect(ApplicationSettings::get('existing'))->toBe('persisted');
    expect(ApplicationSettings::has('new_key'))->toBeFalse();
    expect(DB::table('settings')->get(['key', 'value'])->map(fn ($row) => (array) $row)->all())
        ->toBe([['key' => 'existing', 'value' => 'persisted']]);

    ApplicationSettings::set($key, 'retried');

    expect(ApplicationSettings::get($key))->toBe('retried');
    expect(DB::table('settings')->where('key', $key)->value('value'))->toBe('retried');
})->with(['existing', 'new_key']);

it('restores persisted settings after a failed deletion', function () {
    DB::table('settings')->insert(['key' => 'existing', 'value' => 'persisted']);
    expect(ApplicationSettings::get('existing'))->toBe('persisted');

    $table = config('application-settings.database.table');
    config()->set('application-settings.database.table', 'missing_settings_table');

    try {
        expect(fn () => ApplicationSettings::delete('existing'))->toThrow(QueryException::class);
    } finally {
        config()->set('application-settings.database.table', $table);
    }

    expect(ApplicationSettings::get('existing'))->toBe('persisted');
    expect(ApplicationSettings::has('existing'))->toBeTrue();
    expect(DB::table($table)->where('key', 'existing')->value('value'))->toBe('persisted');

    ApplicationSettings::delete('existing');

    expect(ApplicationSettings::has('existing'))->toBeFalse();
    expect(DB::table($table)->where('key', 'existing')->exists())->toBeFalse();
});

it('refreshes lazily without invalidating the shared cache', function () {
    DB::table('settings')->insert(['key' => 'test_key', 'value' => 'original']);
    expect(ApplicationSettings::get('test_key'))->toBe('original');

    // Simulate another process publishing a newer cached snapshot.
    Cache::forever(config('application-settings.cache.key'), collect([
        (object) ['key' => 'test_key', 'value' => 'external'],
    ]));
    expect(ApplicationSettings::get('test_key'))->toBe('original');

    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        ApplicationSettings::refresh();
        expect(Cache::has(config('application-settings.cache.key')))->toBeTrue();
        expect(ApplicationSettings::get('test_key'))->toBe('external');
        expect(DB::getQueryLog())->toBeEmpty();
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }
});

it('reloads settings at worker lifecycle boundaries', function (string $event) {
    $payload = [];
    if ($event === JobProcessing::class) {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('payload')->andReturn([]);
        $payload = [new JobProcessing('testing', $job)];
    }

    DB::table('settings')->insert(['key' => 'test_key', 'value' => 'original']);
    expect(ApplicationSettings::get('test_key'))->toBe('original');

    // Simulate another process updating the database and invalidating the cache.
    DB::table('settings')->where('key', 'test_key')->update(['value' => 'external']);
    Cache::forget(config('application-settings.cache.key'));
    expect(ApplicationSettings::get('test_key'))->toBe('original');

    Event::dispatch($event, $payload);

    // Resetting is lazy: dispatching the event has not repopulated the cache.
    expect(Cache::has(config('application-settings.cache.key')))->toBeFalse();
    expect(ApplicationSettings::get('test_key'))->toBe('external');

    DB::table('settings')->where('key', 'test_key')->update(['value' => 'later']);
    Cache::forget(config('application-settings.cache.key'));
    expect(ApplicationSettings::get('test_key'))->toBe('external');

    Event::dispatch($event, $payload);
    expect(ApplicationSettings::get('test_key'))->toBe('later');
})->with([
    JobProcessing::class,
    'Laravel\Octane\Events\RequestReceived',
    'Laravel\Octane\Events\TaskReceived',
    'Laravel\Octane\Events\TickReceived',
]);

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

it('preserves literal keys and defaults when reading multiple settings', function () {
    DB::table('settings')->insert([
        ['key' => 'site.name', 'value' => 'Example'],
        ['key' => 'empty', 'value' => ''],
    ]);

    // Existing installations may allow nulls even though the published schema does not.
    Cache::forever(config('application-settings.cache.key'), DB::table('settings')->get()->push(
        (object) ['key' => 'nullable', 'value' => null],
    ));

    expect(ApplicationSettings::get(
        ['site.name', 'empty', 'nullable', 'missing', 'without_default'],
        ['site.name' => 'ignored', 'empty' => 'ignored', 'nullable' => 'ignored', 'missing' => 'fallback'],
    ))->toBe([
        'site.name' => 'Example',
        'empty' => '',
        'nullable' => null,
        'missing' => 'fallback',
        'without_default' => null,
    ]);

    expect(ApplicationSettings::get('nullable', 'fallback'))->toBeNull();
    expect(ApplicationSettings::get('missing', 'fallback'))->toBe('fallback');
    expect(ApplicationSettings::get(['missing'], 'fallback'))->toBe(['missing' => null]);
    expect(ApplicationSettings::get([]))->toBe('');
});

it('reads existing cached rows using configured column names', function () {
    config()->set('application-settings.database.key', 'setting_name');
    config()->set('application-settings.database.value', 'setting_value');
    config()->set('application-settings.database.table', 'missing_settings_table');

    Cache::forever(config('application-settings.cache.key'), collect([
        (object) ['setting_name' => 'site.name', 'setting_value' => 'Cached'],
    ]));

    expect(ApplicationSettings::get('site.name'))->toBe('Cached');
    expect(ApplicationSettings::all())->toBe(['site.name' => 'Cached']);
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

it('deletes settings created after the local snapshot was loaded', function () {
    expect(ApplicationSettings::has('external'))->toBeFalse();

    DB::table('settings')->insert(['key' => 'external', 'value' => 'created elsewhere']);

    ApplicationSettings::delete('external');

    expect(DB::table('settings')->where('key', 'external')->exists())->toBeFalse();
    expect(Cache::has(config('application-settings.cache.key')))->toBeFalse();
    expect(ApplicationSettings::has('external'))->toBeFalse();
});

it('deletes settings without loading the settings table first', function (string $key) {
    DB::table('settings')->insert(['key' => 'existing', 'value' => 'persisted']);
    DB::enableQueryLog();
    DB::flushQueryLog();

    try {
        expect(ApplicationSettings::delete($key))->toBeNull();
        $queries = DB::getQueryLog();
        expect($queries)->toHaveCount(1);
        expect(strtolower($queries[0]['query']))->toStartWith('delete ');
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }

    expect(ApplicationSettings::has($key))->toBeFalse();
})->with(['existing', 'missing']);

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
