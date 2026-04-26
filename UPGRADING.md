# Upgrading Guide

## Upgrading to V1.0

V1.0 is a major release that drops older Laravel and PHP versions and moves to the latest `laravel-query-builder` v7. Follow the steps below carefully.

---

### Requirements

| Requirement | Minimum version |
|---|---|
| PHP | 8.3+ |
| Laravel | 12 or 13 |
| spatie/laravel-query-builder | ^7.0 |

> **Note:** Support for Laravel 10 and 11 has been dropped in V1.0.

---

### Step 1 — Update `composer.json`

Bump the package constraints in your `composer.json`:

```json
"require": {
    "ahmmmmad11/filters": "^1.0",
    "spatie/laravel-query-builder": "^7.0"
}
```

Then pull the updated dependencies:

```bash
composer update ahmmmmad11/filters spatie/laravel-query-builder
```

---

### Step 2 — Update `allowedFilters`, `allowedIncludes`, `allowedSorts`, and `allowedFields` calls

`laravel-query-builder` v7 changed these methods from accepting a single array argument to variadic arguments. You must update every call inside your filter classes.

**Before (v6 syntax):**

```php
QueryBuilder::for(User::class)
    ->allowedFilters(['name', 'email'])
    ->allowedSorts(['name', 'created_at'])
    ->allowedIncludes(['posts', 'comments'])
    ->allowedFields(['id', 'name', 'email']);
```

**After (v7 syntax):**

```php
QueryBuilder::for(User::class)
    ->allowedFilters('name', 'email')
    ->allowedSorts('name', 'created_at')
    ->allowedIncludes('posts', 'comments')
    ->allowedFields('id', 'name', 'email');
```

If you are building the list dynamically, spread the array with `...`:

```php
$filters = ['name', 'email', 'status'];

QueryBuilder::for(User::class)
    ->allowedFilters(...$filters);
```

> **Tip:** Run a project-wide search for `->allowedFilters([` to find every occurrence that needs updating.

---

### Step 3 — Re-publish the config (optional)

If you have published the config file, no changes are required — the keys remain the same. If you want a fresh copy, re-publish with:

```bash
php artisan vendor:publish --tag="filters-config" --force
```

---

### Step 4 — Verify your setup

Run your test suite to make sure everything works after the upgrade:

```bash
php artisan test
# or
composer test
```

---

### Changelog

For a full list of changes in V1.0, see [CHANGELOG.md](CHANGELOG.md).
