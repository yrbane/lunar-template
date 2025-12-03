<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class FilterArrayFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'filter';
    }

    /**
     * @return array<mixed>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $key = $args[0] ?? null;
        $filterValue = $args[1] ?? null;

        if ($key === null) {
            // Filter empty values
            return array_values(array_filter($value));
        }

        $result = [];
        foreach ($value as $item) {
            $itemValue = null;

            if (\is_array($item) && isset($item[$key])) {
                $itemValue = $item[$key];
            } elseif (\is_object($item) && isset($item->$key)) {
                $itemValue = $item->$key;
            }

            if ($filterValue !== null) {
                if ($itemValue === $filterValue) {
                    $result[] = $item;
                }
            } elseif (!empty($itemValue)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
