<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate HTML checkbox input.
 *
 * Usage:
 * - ##checkbox("agree")## - Basic checkbox
 * - ##checkbox("agree", true)## - Checked
 * - ##checkbox("agree", true, "1", "I agree")## - With label
 */
final class CheckboxMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'checkbox';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $checked = (bool) ($args[1] ?? false);
        $value = (string) ($args[2] ?? '1');
        $label = (string) ($args[3] ?? '');
        $class = (string) ($args[4] ?? '');

        if ($name === '') {
            return '';
        }

        $checkedAttr = $checked ? ' checked' : '';
        $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';

        $input = '<input type="checkbox" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" ';
        $input .= 'id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" ';
        $input .= 'value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $classAttr . $checkedAttr . '>';

        if ($label !== '') {
            return '<label>' . $input . ' ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>';
        }

        return $input;
    }
}
