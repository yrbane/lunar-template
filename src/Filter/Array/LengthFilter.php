<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Countable;
use Lunar\Template\Filter\FilterInterface;

final class LengthFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'length';
    }

    public function apply(mixed $value, array $args = []): int
    {
        if (\is_array($value) || $value instanceof Countable) {
            return \count($value);
        }

        if (\is_string($value)) {
            return mb_strlen($value);
        }

        return 0;
    }
}
