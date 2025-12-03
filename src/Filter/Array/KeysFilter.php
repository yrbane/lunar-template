<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class KeysFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'keys';
    }

    /**
     * @return array<int, int|string>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            return [];
        }

        return array_keys($value);
    }
}
