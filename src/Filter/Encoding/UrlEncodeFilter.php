<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use Lunar\Template\Filter\AbstractFilter;

final class UrlEncodeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'url_encode';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $raw = (bool) ($args[0] ?? false);

        if ($raw) {
            return rawurlencode($this->toString($value));
        }

        return urlencode($this->toString($value));
    }
}
