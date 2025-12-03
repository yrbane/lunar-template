<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate HTML select element.
 *
 * Usage:
 * - ##select("country", countries)## - From array
 * - ##select("country", countries, "FR")## - With selected value
 * - ##select("country", countries, "FR", "Choose...")## - With placeholder
 */
final class SelectMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'select';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $options = $args[1] ?? [];
        $selected = $args[2] ?? null;
        $placeholder = (string) ($args[3] ?? '');
        $class = (string) ($args[4] ?? '');

        if ($name === '' || !\is_array($options)) {
            return '';
        }

        $attributes = [
            'name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"',
            'id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"',
        ];

        if ($class !== '') {
            $attributes[] = 'class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';
        }

        $html = '<select ' . implode(' ', $attributes) . '>';

        if ($placeholder !== '') {
            $html .= '<option value="">' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '</option>';
        }

        foreach ($options as $value => $label) {
            $isSelected = $selected !== null && (string) $value === (string) $selected;
            $selectedAttr = $isSelected ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"' . $selectedAttr . '>';
            $html .= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8');
            $html .= '</option>';
        }

        $html .= '</select>';

        return $html;
    }
}
