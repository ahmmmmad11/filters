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
    public function paginate($rows = 30)
    {
        $this->load();

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
