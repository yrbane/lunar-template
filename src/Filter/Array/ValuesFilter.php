<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class ValuesFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'values';
    }

    /**
     * @return array<int, mixed>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            return [];
        }

        return array_values($value);
    }
}
