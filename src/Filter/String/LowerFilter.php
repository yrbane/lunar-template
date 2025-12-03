<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class LowerFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'lower';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return mb_strtolower($this->toString($value));
    }
}
