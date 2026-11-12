# Changelog

All notable changes to `robtrehy/laravel-application-settings` will be documented in this file.

## 5.0.0 - 2026-11-12

Version 5 modernises the package using Spatie’s Laravel package skeleton and improves the reliability of settings storage and caching.

### Requirements

- PHP 8.4 or later.
- Laravel 12 or 13.

Support for PHP 8.2, PHP 8.3, and Laravel 11 has been dropped.

### Reliability and performance

- Updating a setting now saves only that key, using two persistence queries regardless of the number of stored settings.
- Deleting a setting no longer loads all settings first.
- Reduced repeated configuration lookups and checks when reading settings.
- Failed loads can be retried within the same process.
- Failed writes and deletions discard local state so subsequent reads do not return unsaved changes.
- Deletion now works for settings created by another process after the local snapshot was loaded.
- Transactional reads bypass caches, and writes invalidate shared cache again after the outermost commit.

### Long-running workers

- Added ApplicationSettings::refresh() to discard local settings and reload on the next access.
- Local settings automatically reset before queue jobs and Octane requests, tasks, and ticks.

### Maintenance

- Adopted Spatie’s Laravel Package Tools skeleton.
- Expanded regression coverage and improved documentation and CI.

Existing installations do not need to republish configuration or rerun the settings migration.

## 4.0.0 - 2025-04-18
- Added support for Laravel 12
- Dropped support for Laravel 10
- Dropped support for Laravel 9

## 3.0.0 - 2024-03-18
- Added support for Laravel 11

## 2.0.2 - 2023-03-27
- ApplicationSettings::get() now supports getting multiple keys

## 2.0.1 - 2023-03-03
- Added support for Laravel 10

## 2.0.0 - 2023-02-10
- Dropped support for Laravel 8
- Added support for Laravel 9
- Required PHP ^8.1

## 1.1.3 - 2022-03-30
- Security Updates

## 1.1.2 - 2021-11-17
- Fixed - Deleting a setting would not reset the cache
- Security Updates
  
## 1.1.1 - 2021-08-02
- Fixed Cache support!
 
## 1.1.0 - 2021-08-02
- Added Cache support

## 1.0.2 - 2021-06-30
- Security Updates

## 1.0.1 - 2021-04-22
- Updated database migration file to index key and support longer values
  
## 1.0.0 - 2021-04-22
- initial release
