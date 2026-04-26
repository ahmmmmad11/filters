<?php

namespace Ahmmmmad11\Filters\Facades;

use Ahmmmmad11\Filters\Filter;
use Illuminate\Support\Facades\Facade;

/**
 * @see Filter
 */
class Filters extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Filter::class;
    }
}
