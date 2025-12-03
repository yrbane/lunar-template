<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class ReplaceFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'replace';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $search = (string) $this->getArg($args, 0, '');
        $replace = (string) $this->getArg($args, 1, '');

        return str_replace($search, $replace, $this->toString($value));
    }
}
