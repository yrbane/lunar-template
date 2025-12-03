<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class PluckFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'pluck';
    }

    /**
     * @return array<mixed>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $key = (string) ($args[0] ?? '');

        if ($key === '') {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (\is_array($item) && \array_key_exists($key, $item)) {
                $result[] = $item[$key];
            } elseif (\is_object($item) && property_exists($item, $key)) {
                $result[] = $item->$key;
            }
        }

        return $result;
    }
}
