<?php

namespace Ahmmmmad11\Filters\Tests\Fixtures\Filters;

use Ahmmmmad11\Filters\Filter;
use Ahmmmmad11\Filters\Tests\Fixtures\Models\User;
use Spatie\QueryBuilder\QueryBuilder;

class UsersFilterForTest extends Filter
{
    public int $filterCalls = 0;

    public function filter(): Filter
    {
        $this->filterCalls++;
        $this->query = QueryBuilder::for(User::query())->allowedFilters(['name', 'status']);

        return $this;
    }
}
