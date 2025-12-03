<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * JSON encode data.
 *
 * Usage:
 * - ##json(data)## - Compact JSON
 * - ##json(data, true)## - Pretty printed JSON
 */
final class JsonMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'json';
    }

    public function execute(array $args): string
    {
        $data = $args[0] ?? null;
        $pretty = (bool) ($args[1] ?? false);

        $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, $flags);
    }
}
