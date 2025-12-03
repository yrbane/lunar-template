<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class TruncateFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'truncate';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $str = $this->toString($value);
        $length = (int) $this->getArg($args, 0, 50);
        $suffix = (string) $this->getArg($args, 1, '...');

        if (mb_strlen($str) <= $length) {
            return $str;
        }

        return mb_substr($str, 0, $length) . $suffix;
    }
}
