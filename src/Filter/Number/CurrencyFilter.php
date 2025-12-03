<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class CurrencyFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'currency';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $symbol = (string) $this->getArg($args, 0, '$');
        $decimals = (int) $this->getArg($args, 1, 2);
        $decPoint = (string) $this->getArg($args, 2, '.');
        $thousandsSep = (string) $this->getArg($args, 3, ',');

        $formatted = number_format((float) $value, $decimals, $decPoint, $thousandsSep);

        return $symbol . $formatted;
    }
}
