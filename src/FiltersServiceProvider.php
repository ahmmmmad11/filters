<?php

namespace ahmmmmad11\Filters;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ahmmmmad11\Filters\Commands\FiltersCommand;

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
            ->hasCommand(FiltersCommand::class);
    }
}
