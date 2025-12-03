<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class TitleFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'title';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return mb_convert_case($this->toString($value), MB_CASE_TITLE);
    }
}
