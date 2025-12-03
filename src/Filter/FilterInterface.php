<?php

declare(strict_types=1);

namespace Lunar\Template\Filter;

/**
 * Interface for template filters.
 *
 * Filters transform variable values in templates using the pipe syntax:
 * [[ variable | filterName ]] or [[ variable | filterName(arg1, arg2) ]]
 */
interface FilterInterface
{
    /**
     * Get the filter name used in templates.
     */
    public function getName(): string;

    /**
     * Apply the filter to a value.
     *
     * @param mixed $value The value to filter
     * @param array<int, mixed> $args Additional arguments passed to the filter
     *
     * @return mixed The filtered value
     */
    public function apply(mixed $value, array $args = []): mixed;
}
