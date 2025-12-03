<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use Lunar\Template\Filter\AbstractFilter;

final class UrlDecodeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'url_decode';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $raw = (bool) ($args[0] ?? false);

        if ($raw) {
            return rawurldecode($this->toString($value));
        }

        return urldecode($this->toString($value));
    }
}
