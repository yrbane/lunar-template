<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class LastFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'last';
    }

    public function apply(mixed $value, array $args = []): mixed
    {
        if (\is_array($value)) {
            return end($value) ?: null;
        }

        if (\is_string($value)) {
            return mb_substr($value, -1);
        }

        return null;
    }
}
