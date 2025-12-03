<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate HTML radio input.
 *
 * Usage:
 * - ##radio("gender", "male", "Male")##
 * - ##radio("gender", "female", "Female", true)## - Checked
 */
final class RadioMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'radio';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $value = (string) ($args[1] ?? '');
        $label = (string) ($args[2] ?? '');
        $checked = (bool) ($args[3] ?? false);
        $class = (string) ($args[4] ?? '');

        if ($name === '') {
            return '';
        }

        $checkedAttr = $checked ? ' checked' : '';
        $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $id = $name . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $value);

        $input = '<input type="radio" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" ';
        $input .= 'id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" ';
        $input .= 'value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"' . $classAttr . $checkedAttr . '>';

        if ($label !== '') {
            return '<label>' . $input . ' ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label>';
        }

        return $input;
    }
}
