<?php

namespace ahmmmmad11\Filters;

use Spatie\QueryBuilder\QueryBuilder;

abstract class Filter
{
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
    public function paginate(?int $rows = null)
    {
        $this->load();

        // if rows not passed get the value from request
        $rows ??= \request()->get('paginate');

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
}
