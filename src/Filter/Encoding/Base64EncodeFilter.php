<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use Lunar\Template\Filter\AbstractFilter;

final class Base64EncodeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'base64_encode';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return base64_encode($this->toString($value));
    }
}
