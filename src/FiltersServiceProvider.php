<?php

namespace Ahmmmmad11\Filters;

use Ahmmmmad11\Filters\Commands\MakeFilter;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FiltersServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filters')
            ->hasConfigFile()
            ->hasCommand(MakeFilter::class);
    }
}
