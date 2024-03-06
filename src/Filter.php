<?php

namespace ahmmmmad11\Filters;

abstract class Filter
{
    protected object|array|null $data = null;

    abstract public function filter(): self;

    public function load(): void
    {
        if (is_null($this->data)) {
            $this->filter();
        }
    }

    public function execute(callable $callable): self
    {
        $this->load();

        $callable($this->data);

        return $this;
    }

    public function paginate($rows = 30)
    {
        $this->load();

        return $this->data?->paginate($rows);
    }

    public function get()
    {
        $this->load();

        return $this->data?->get();
    }
}
