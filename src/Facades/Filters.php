<?php

namespace Ahmmmmad11\Filters\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ahmmmmad11\Filters\Filter
 */
class Filters extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ahmmmmad11\Filters\Filter::class;
    }
}
