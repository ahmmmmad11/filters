<?php

namespace ahmmmmad11\Filters;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Traits\ForwardsCalls;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @mixin QueryBuilder
**/
abstract class Filter
{
    use ForwardsCalls;

    protected ?QueryBuilder $query = null;

    abstract public function filter(): self;

    /**
     * load model data if not been loaded
     **/
    public function load(): void
    {
        if (is_null($this->query)) {
            $this->filter();
        }
    }

    /**
     * execute custom user logic
     **/
    public function execute(callable $callable): self
    {
        $this->load();

        $callable($this->query);

        return $this;
    }

    /**
     * get data as pagination
     **/
    public function paginate(?int $rows = null): ?LengthAwarePaginator
    {
        $this->load();

        // if rows not passed get the value from request
        $rows ??= \request()->get('per_page');

        // fallback to default rows length if rows is null
        $rows ??= config('filters.rows');

        return $this->query?->paginate($rows);
    }

    /**
     * get all filtered data
     **/
    public function get(): \Illuminate\Database\Eloquent\Collection|array|null
    {
        $this->load();

        return $this->query?->get();
    }

    /**
     * prepare the query if not prepared
    **/
    public function prepare(): void
    {
        if (is_null($this->query)) {
            $this->filter();
        }
    }

    /**
     * forward calls to query builder instance
     **/
    public function __call(string $name, array $arguments)
    {
        $this->prepare();

        $this->forwardCallTo($this->query, $name, $arguments);

        return $this;
    }
}
