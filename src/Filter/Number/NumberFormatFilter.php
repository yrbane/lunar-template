<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class NumberFormatFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'number_format';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $decimals = (int) $this->getArg($args, 0, 0);
        $decPoint = (string) $this->getArg($args, 1, '.');
        $thousandsSep = (string) $this->getArg($args, 2, ',');

        return number_format((float) $value, $decimals, $decPoint, $thousandsSep);
    }
}
