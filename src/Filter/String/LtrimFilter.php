<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class LtrimFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'ltrim';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $characters = $this->getArg($args, 0);

        if (\is_string($characters)) {
            return ltrim($this->toString($value), $characters);
        }

        return ltrim($this->toString($value));
    }
}
