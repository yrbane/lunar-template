<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class ShuffleFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'shuffle';
    }

    /**
     * @return array<mixed>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $shuffled = $value;
        shuffle($shuffled);

        return $shuffled;
    }
}
