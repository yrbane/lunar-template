<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class OrdinalFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'ordinal';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $num = (int) $value;
        $abs = abs($num);

        if ($abs % 100 >= 11 && $abs % 100 <= 13) {
            return $num . 'th';
        }

        return match ($abs % 10) {
            1 => $num . 'st',
            2 => $num . 'nd',
            3 => $num . 'rd',
            default => $num . 'th',
        };
    }
}
