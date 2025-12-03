<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class FloorFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'floor';
    }

    public function apply(mixed $value, array $args = []): float
    {
        return floor((float) $value);
    }
}
