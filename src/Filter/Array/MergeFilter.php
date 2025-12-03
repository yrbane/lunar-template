<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class MergeFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'merge';
    }

    /**
     * @return array<mixed>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            $value = [];
        }

        foreach ($args as $arg) {
            if (\is_array($arg)) {
                $value = array_merge($value, $arg);
            }
        }

        return $value;
    }
}
