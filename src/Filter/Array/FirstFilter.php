<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class FirstFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'first';
    }

    public function apply(mixed $value, array $args = []): mixed
    {
        if (\is_array($value)) {
            return reset($value) ?: null;
        }

        if (\is_string($value)) {
            return mb_substr($value, 0, 1);
        }

        return null;
    }
}
