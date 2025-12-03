<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

final class SpacelessFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'spaceless';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $str = $this->toString($value);

        // Remove whitespace between HTML tags
        return (string) preg_replace('/>\s+</', '><', $str);
    }
}
