<?php

namespace RobTrehy\LaravelApplicationSettings\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RobTrehy\LaravelApplicationSettings\ApplicationSettings;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_can_set_and_get_a_setting()
    {
        ApplicationSettings::set('test_key', 'test_value');
        $value = ApplicationSettings::get('test_key');

        $this->assertEquals('test_value', $value);
    }

    /** @test */
    public function test_can_set_and_get_mutliple_settings()
    {
        ApplicationSettings::set('test_key', 'test_value');
        $values = ApplicationSettings::get(['test_key', 'test_key2']);

        $this->assertCount(2, $values);
        $this->assertArrayHasKey('test_key', $values);
        $this->assertArrayHasKey('test_key2', $values);
        $this->assertArrayNotHasKey('key_not_set', $values);
    }

    /** @test */
    public function test_can_get_all_settings()
    {
        ApplicationSettings::set('test_key', 'test_value');
        ApplicationSettings::set('test_key2', 'test_value2');
        ApplicationSettings::set('last_key', 'last_value');

        $settings = ApplicationSettings::all();

        $this->assertCount(3, $settings);
        $this->assertArrayHasKey('test_key', $settings);
        $this->assertArrayHasKey('last_key', $settings);
        $this->assertArrayNotHasKey('key_not_set', $settings);
    }

    /** @test */
    public function test_can_check_if_setting_is_not_set()
    {
        $value = ApplicationSettings::has('key_not_set');

        $this->assertIsBool($value);
        $this->assertFalse($value);
    }

    /** @test */
    public function test_can_check_if_setting_is_set()
    {
        ApplicationSettings::set('key_set', 'some_value');
        $value = ApplicationSettings::has('key_set');

        $this->assertIsBool($value);
        $this->assertTrue($value);
    }

    /** @test */
    public function test_can_delete_a_setting()
    {
        ApplicationSettings::set('key_set', 'some_value');
        ApplicationSettings::delete('key_set');
        $value = ApplicationSettings::has('key_set');

        $this->assertIsBool($value);
        $this->assertFalse($value);
    }
}
