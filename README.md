# This package serves as a wrapper over the popular spatie query builder. Its primary purpose is to quickly scaffold filters for your application.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ahmmmmad11/filters.svg?style=flat-square)](https://packagist.org/packages/ahmmmmad11/filters)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ahmmmmad11/filters/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ahmmmmad11/filters/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ahmmmmad11/filters.svg?style=flat-square)](https://packagist.org/packages/ahmmmmad11/filters)


## Installation

You can install the package via composer:

```bash
composer require ahmmmmad11/filters
```

After installation, you can publish the configuration file using the following command:

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
    | The `per_page` value indicates the default pagination size. 
    | If the 'per_page' argument is not provided or there is no 
    | paginate query in the request, this value will be used.
    |
    */

    'per_page' => 100,
];
```

## Usage

### create your first filter

```bash
php artisan filter:make UsersFilter --model=User
```

This will generate a `UsersFilter` class in the ‘app/Http/Filters’ directory. You can then customize this filter according to your application’s needs.

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

Certainly! Here’s a rephrased version of your instructions:

If you follow the correct naming convention, you can omit the `--model` option. For instance, if you have a `User` model, you can use the following simplified command:

```bash
    php artisan filter:make UsersFilter
```

In this case, since you used the plural form of the model (`User` becomes “Users”), or if you prefer the singular form (UserFilter), the filter command will automatically associate it with the User model.

> Please note that this rule does not apply to combined model names like `UserProduct`. In such cases, please explicitly specify the model using the `--model=UserProduct` option or the shorter `-m"UserProduct"` form.

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

    // https://example.com/users?per_page=10
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

or you can directly chain eloquent methods

```php
public function index(UsersFilter $filter)
{
    return $filter->where('status', 'active')->get();
}
```

### Include relations

to include model relations just add option `--relations` to filter make command.

```bash
php artisan filter:make UsersFilter  --relations
```

or short form

```bash
php artisan filter:make UsersFilter -r
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

if you fond any security vulnerability send a direct email to me alamerahmed00@gmail.com


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
