<?php

declare(strict_types=1);

namespace Lunar\Template\Filter;

/**
 * Base class for filters providing common functionality.
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * Convert value to string safely.
     */
    protected function toString(mixed $value): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (is_numeric($value) || (\is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (\is_null($value)) {
            return '';
        }

        if (\is_array($value)) {
            return implode(', ', array_map(fn ($v) => $this->toString($v), $value));
        }

        return '';
    }

    /**
     * Get argument with default value.
     *
     * @param array<int, mixed> $args
     */
    protected function getArg(array $args, int $index, mixed $default = null): mixed
    {
        return $args[$index] ?? $default;
    }
}
