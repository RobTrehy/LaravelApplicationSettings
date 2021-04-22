<?php

namespace RobTrehy\LaravelApplicationSettings\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RobTrehy\LaravelApplicationSettings\ApplicationSettings;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testCanSetAndGetASetting()
    {
        ApplicationSettings::set('test_key', 'test_value');
        $value = ApplicationSettings::get('test_key');

        $this->assertEquals('test_value', $value);
    }

    /** @test */
    public function testCanGetAllSettings()
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
    public function testCanCheckIfSettingIsNotSet()
    {
        $value = ApplicationSettings::has('key_not_set');

        $this->assertIsBool($value);
        $this->assertFalse($value);
    }

    /** @test */
    public function testCanCheckIfSettingIsSet()
    {
        ApplicationSettings::set('key_set', 'some_value');
        $value = ApplicationSettings::has('key_set');

        $this->assertIsBool($value);
        $this->assertTrue($value);
    }

    /** @test */
    public function testCanDeleteASetting()
    {
        ApplicationSettings::set('key_set', 'some_value');
        ApplicationSettings::delete('key_set');
        $value = ApplicationSettings::has('key_set');

        $this->assertIsBool($value);
        $this->assertFalse($value);
    }
}
