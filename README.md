# A wrapper package over spatie query builder to quickly scaffold filters.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ahmmmmad11/filters.svg?style=flat-square)](https://packagist.org/packages/ahmmmmad11/filters)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ahmmmmad11/filters/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ahmmmmad11/filters/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ahmmmmad11/filters.svg?style=flat-square)](https://packagist.org/packages/ahmmmmad11/filters)


## Installation

You can install the package via composer:

```bash
composer require ahmmmmad11/filters
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filters-config"
```

This is the contents of the published config file:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Filters Path
    |--------------------------------------------------------------------------
    |
    | This value is the path where your filter class will be created.
    |
    */

    'path' => '\Http\Filters',

    /*
    |--------------------------------------------------------------------------
    | pagination rows
    |--------------------------------------------------------------------------
    |
    | This value is the rows represents the default pagination size if the
    | rows' argument is not passed or no paginate query in the request.
    |
    */

    'rows' => 100,
];
```

## Usage

### create your first filter

```bash
php artisan filter:make UsersFilter --model=User
```

this will generate `UsersFilter` class at 'app/Http/Filters' directory.

```php
    <?php

    namespace App\Http\Filters;
    
    use Ahmmmmad11\Filters\Filter;
    use App\Models\User;
    use Spatie\QueryBuilder\QueryBuilder;
    
    class UsersFilter extends Filter
    {
        public function filter(): Filter
        {
            $this->data = QueryBuilder::for(User::class)
                ->allowedFilters(
                    ["id","name","email","email_verified_at","created_at","updated_at"]
                );
    
            return $this;
        }
    }
```

now you can use the filter by injecting `UserFilter` in your controller like:

```php
...

use App\Http\Filters\UsersFilter;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UsersFilter $filter)
    {
        return $filter->get();
    }
}
```

or you may want to return paginated data

```php
    ...
    
    public function index(UsersFilter $filter)
    {
        return $filter->paginate(30);
    }

```

if you want to assign the size of the pagination form the client side you can do it by leaving the paginate argument empty
```php
    $filter->paginate();

    // https://example.com/users?paingate=10
```

> if the `rows` argument of `paginate` method is left empty and no `?paginate` in request query the default row size in `filters.php` config will be used.

### Extend Eloquent methods
you can preform customization over the query directly from you controller method by passing `callback` to `execute` method.

```php
// UsersController

public function index(UsersFilter $filter)
{
    return $filter->execute(function ($query) {
        $query->where('status', 'active');
    })->get();
}
```

> inside `execute` callback function you can use all eloquent methods.

### Include relations

to include relations in the filter class just add option `--relations` to filter make command.

```bash
php artisan filter:make UsersFilter --model=User --relations
```

this will generate:

```php

//UserFilter Class

public function filter(): Filter
    {
        $this->data = QueryBuilder::for(User::class)
            ->allowedFilters(
                [...filters]
            )
            ->allowedIncludes(
                [...relations]
            );

        return $this;
    }
```

## for more details check [Spatie Laravel-query-builder](https://spatie.be/docs/laravel-query-builder/v5/introduction)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ahmed Mohamed](https://github.com/ahmmmmad11)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
