<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use Lunar\Template\Filter\AbstractFilter;

final class Sha1Filter extends AbstractFilter
{
    public function getName(): string
    {
        return 'sha1';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return sha1($this->toString($value));
    }
}
