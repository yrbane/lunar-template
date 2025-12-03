<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class ReverseFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'reverse';
    }

    /**
     * @return string|array<mixed>
     */
    public function apply(mixed $value, array $args = []): string|array
    {
        if (\is_array($value)) {
            return array_reverse($value);
        }

        $str = $this->toString($value);
        $chars = mb_str_split($str);

        return implode('', array_reverse($chars));
    }
}
