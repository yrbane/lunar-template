<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class AbsFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'abs';
    }

    public function apply(mixed $value, array $args = []): int|float
    {
        return abs(\is_int($value) ? $value : (float) $value);
    }
}
