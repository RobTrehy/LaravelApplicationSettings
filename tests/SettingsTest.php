<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use RobTrehy\LaravelApplicationSettings\ApplicationSettings;

uses(RefreshDatabase::class);

it('can set and get a setting', function () {
    ApplicationSettings::set('test_key', 'test_value');
    $value = ApplicationSettings::get('test_key');

    expect($value)->toBe('test_value');
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
