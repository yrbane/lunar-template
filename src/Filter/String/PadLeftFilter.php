<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class PadLeftFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'pad_left';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $length = (int) $this->getArg($args, 0, 0);
        $pad = (string) $this->getArg($args, 1, ' ');

        return str_pad($this->toString($value), $length, $pad, STR_PAD_LEFT);
    }
}
