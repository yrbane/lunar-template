<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class RtrimFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'rtrim';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $characters = $this->getArg($args, 0);

        if (\is_string($characters)) {
            return rtrim($this->toString($value), $characters);
        }

        return rtrim($this->toString($value));
    }
}
