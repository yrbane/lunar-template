<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class CeilFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'ceil';
    }

    public function apply(mixed $value, array $args = []): float
    {
        return ceil((float) $value);
    }
}
