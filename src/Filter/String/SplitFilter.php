<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class SplitFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'split';
    }

    /**
     * @return array<int, string>
     */
    public function apply(mixed $value, array $args = []): array
    {
        $delimiter = (string) $this->getArg($args, 0, '');
        $limit = $this->getArg($args, 1);

        $str = $this->toString($value);

        if ($delimiter === '') {
            return mb_str_split($str);
        }

        if (\is_int($limit)) {
            return explode($delimiter, $str, $limit);
        }

        return explode($delimiter, $str);
    }
}
