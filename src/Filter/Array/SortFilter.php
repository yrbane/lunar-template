<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class SortFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'sort';
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
        $direction = strtolower((string) ($args[1] ?? 'asc'));

        $sorted = $value;

        if (\is_string($key) && $key !== '') {
            usort($sorted, function ($a, $b) use ($key, $direction) {
                $aVal = \is_array($a) ? ($a[$key] ?? null) : (\is_object($a) ? ($a->$key ?? null) : null);
                $bVal = \is_array($b) ? ($b[$key] ?? null) : (\is_object($b) ? ($b->$key ?? null) : null);

                $result = $aVal <=> $bVal;

                return $direction === 'desc' ? -$result : $result;
            });
        } else {
            if ($direction === 'desc') {
                rsort($sorted);
            } else {
                sort($sorted);
            }
        }

        return $sorted;
    }
}
