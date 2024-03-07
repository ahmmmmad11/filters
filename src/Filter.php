<?php

namespace ahmmmmad11\Filters;

abstract class Filter
{
    protected object|array|null $data = null;

    abstract public function filter(): self;

    /**
     * load model data if not been loaded
     **/
    public function load(): void
    {
        if (is_null($this->data)) {
            $this->filter();
        }
    }

    /**
     * execute custom user logic
     **/
    public function execute(callable $callable): self
    {
        $this->load();

        $callable($this->data);

        return $this;
    }

    /**
     * get data as pagination
     **/
    public function paginate(int $rows = null)
    {
        $this->load();

        // if rows not passed get the value from request
        $rows??= \request()->get('paginate');

        // fallback to default rows length if rows is null
        $rows??= config('filters.rows');

        return $this->data?->paginate($rows);
    }

    /**
     * get all filtered data
     **/
    public function get()
    {
        $this->load();

        return $this->data?->get();
    }
}
