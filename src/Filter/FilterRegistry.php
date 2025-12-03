<?php

declare(strict_types=1);

namespace Lunar\Template\Filter;

use Lunar\Template\Exception\TemplateException;

/**
 * Registry for managing template filters.
 */
final class FilterRegistry
{
    /** @var array<string, FilterInterface|callable> */
    private array $filters = [];

    /**
     * Register a filter using a callable.
     */
    public function register(string $name, callable $callback): self
    {
        $this->filters[$name] = $callback;

        return $this;
    }

    /**
     * Register a filter instance.
     */
    public function registerInstance(FilterInterface $filter): self
    {
        $this->filters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * Check if a filter is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->filters[$name]);
    }

    /**
     * Get a filter by name.
     *
     * @throws TemplateException If filter is not registered
     *
     * @return FilterInterface|callable
     */
    public function get(string $name): FilterInterface|callable
    {
        if (!$this->has($name)) {
            throw new TemplateException("Filter '$name' is not registered");
        }

        return $this->filters[$name];
    }

    /**
     * Apply a filter to a value.
     *
     * @param string $name Filter name
     * @param mixed $value Value to filter
     * @param array<int, mixed> $args Additional arguments
     *
     * @throws TemplateException If filter is not registered
     *
     * @return mixed Filtered value
     */
    public function apply(string $name, mixed $value, array $args = []): mixed
    {
        $filter = $this->get($name);

        if ($filter instanceof FilterInterface) {
            return $filter->apply($value, $args);
        }

        return $filter($value, ...$args);
    }

    /**
     * Get all registered filter names.
     *
     * @return array<int, string>
     */
    public function getNames(): array
    {
        return array_keys($this->filters);
    }

    /**
     * Remove a filter.
     */
    public function remove(string $name): self
    {
        unset($this->filters[$name]);

        return $this;
    }

    /**
     * Remove all filters.
     */
    public function clear(): self
    {
        $this->filters = [];

        return $this;
    }

    /**
     * Get the number of registered filters.
     */
    public function count(): int
    {
        return \count($this->filters);
    }
}
