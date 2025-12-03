<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class RepeatFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'repeat';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $times = (int) $this->getArg($args, 0, 1);

        return str_repeat($this->toString($value), max(0, $times));
    }
}
