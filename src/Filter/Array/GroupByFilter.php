<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class GroupByFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'group_by';
    }

    /**
     * @return array<string, array<mixed>>
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
            $groupKey = '';

            if (\is_array($item) && isset($item[$key])) {
                $groupKey = (string) $item[$key];
            } elseif (\is_object($item) && isset($item->$key)) {
                $groupKey = (string) $item->$key;
            }

            if (!isset($result[$groupKey])) {
                $result[$groupKey] = [];
            }

            $result[$groupKey][] = $item;
        }

        return $result;
    }
}
