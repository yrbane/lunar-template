<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class WordwrapFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'wordwrap';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $width = (int) $this->getArg($args, 0, 75);
        $break = (string) $this->getArg($args, 1, "\n");
        $cut = (bool) $this->getArg($args, 2, false);

        return wordwrap($this->toString($value), $width, $break, $cut);
    }
}
