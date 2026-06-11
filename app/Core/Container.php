<?php
namespace App\Core;

class Container
{
    private array $bindings = [];

    public function bind(string $abstract, callable $factory): void
    {
        $this->bindings[$abstract] = $factory;
    }

    public function make(string $abstract): mixed
    {
        if (!isset($this->bindings[$abstract])) {
            throw new \RuntimeException("No binding for: $abstract");
        }
        return ($this->bindings[$abstract])($this);
    }
}
