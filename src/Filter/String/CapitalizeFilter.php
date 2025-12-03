<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class CapitalizeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'capitalize';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $str = $this->toString($value);

        if ($str === '') {
            return '';
        }

        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_strtolower(mb_substr($str, 1));
    }
}
