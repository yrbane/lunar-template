<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use JsonException;
use Lunar\Template\Filter\FilterInterface;

final class JsonEncodeFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'json_encode';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $pretty = (bool) ($args[0] ?? false);

        $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE;

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        try {
            return json_encode($value, $flags);
        } catch (JsonException) {
            return '{}';
        }
    }
}
