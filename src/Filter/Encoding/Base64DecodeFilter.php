<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use Lunar\Template\Filter\AbstractFilter;

final class Base64DecodeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'base64_decode';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $decoded = base64_decode($this->toString($value), true);

        return $decoded !== false ? $decoded : '';
    }
}
