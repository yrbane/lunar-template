<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class PercentFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'percent';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $decimals = (int) $this->getArg($args, 0, 0);
        $multiply = (bool) $this->getArg($args, 1, true);

        $num = (float) $value;

        if ($multiply) {
            $num *= 100;
        }

        return number_format($num, $decimals) . '%';
    }
}
