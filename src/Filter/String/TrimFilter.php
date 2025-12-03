<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class TrimFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'trim';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $characters = $this->getArg($args, 0);

        if (\is_string($characters)) {
            return trim($this->toString($value), $characters);
        }

        return trim($this->toString($value));
    }
}
