<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate hidden input field.
 *
 * Usage:
 * - ##hidden("user_id", 123)##
 * - ##hidden("_method", "PUT")## - Method spoofing
 */
final class HiddenMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'hidden';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $value = (string) ($args[1] ?? '');

        if ($name === '') {
            return '';
        }

        return '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
    }
}
