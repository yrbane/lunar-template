<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class UpperFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'upper';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return mb_strtoupper($this->toString($value));
    }
}
