<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class UniqueFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'unique';
    }

    /**
     * @return array<mixed>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            return [];
        }

        return array_values(array_unique($value, SORT_REGULAR));
    }
}
