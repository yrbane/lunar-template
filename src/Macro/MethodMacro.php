<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate method spoofing hidden field.
 *
 * Usage:
 * - ##method("PUT")##
 * - ##method("DELETE")##
 * - ##method("PATCH")##
 */
final class MethodMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'method';
    }

    public function execute(array $args): string
    {
        $method = strtoupper((string) ($args[0] ?? 'POST'));

        $allowed = ['PUT', 'PATCH', 'DELETE'];
        if (!\in_array($method, $allowed, true)) {
            return '';
        }

        return '<input type="hidden" name="_method" value="' . $method . '">';
    }
}
