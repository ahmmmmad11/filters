<?php

namespace ahmmmmad11\Filters\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ahmmmmad11\Filters\Filters
 */
class Filters extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \ahmmmmad11\Filters\Filters::class;
    }
}
