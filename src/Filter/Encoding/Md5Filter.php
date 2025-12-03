<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use Lunar\Template\Filter\AbstractFilter;

final class Md5Filter extends AbstractFilter
{
    public function getName(): string
    {
        return 'md5';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return md5($this->toString($value));
    }
}
