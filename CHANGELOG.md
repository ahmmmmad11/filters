# Changelog

All notable changes to `filters` will be documented in this file.

## 1.0.0 - 2026-04-26

### Breaking Changes

- **Dropped support for Laravel 10 and 11.** Laravel 12 and 13 are now required.
- **Dropped support for PHP < 8.3.** PHP 8.3+ is now required.
- **Upgraded to `spatie/laravel-query-builder` v7.** The `allowedFilters`, `allowedSorts`, `allowedIncludes`, and `allowedFields` methods now accept variadic arguments instead of a single array. Update all filter classes accordingly — see [UPGRADING.md](UPGRADING.md) for a step-by-step guide.

### Added

- Full Pest test suite covering positive and negative cases for `Filter` runtime behavior and the `filter:make` artisan command.

---

## 0.4.4 - 2025-05-12

- Bump dependencies.

## 0.4.3 - 2024-04-14

- Infer model name from filter name argument (e.g. `UsersFilter` → `User` model).
- Added short option `-m` as alias for `--model`.
- Added short option `-r` as alias for `--relations`.
- Enhanced documentation.

## 0.3.0 - 2024-03-29

- Support for Laravel 11.

## 0.2.0 - 2024-03-08

- Renamed `data` property to `query` in the base `Filter` class.
- Renamed config key `rows` to `per_page`.
- Enhanced documentation.

## 0.1.1 - 2024-03-07

First release:

- Auto-generate filter class with table fields via `filter:make`.
- Include model relations in the generated class via the `--relations` option.
