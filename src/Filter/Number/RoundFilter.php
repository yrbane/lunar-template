<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class RoundFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'round';
    }

    public function apply(mixed $value, array $args = []): float
    {
        $precision = (int) $this->getArg($args, 0, 0);

        return round((float) $value, $precision);
    }
}
