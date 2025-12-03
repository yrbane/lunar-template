<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate HTML label element.
 *
 * Usage:
 * - ##label("email", "Email Address")##
 * - ##label("email", "Email Address", true)## - Required
 */
final class LabelMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'label';
    }

    public function execute(array $args): string
    {
        $for = (string) ($args[0] ?? '');
        $text = (string) ($args[1] ?? '');
        $required = (bool) ($args[2] ?? false);
        $class = (string) ($args[3] ?? '');

        if ($for === '' || $text === '') {
            return '';
        }

        $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $requiredMark = $required ? ' <span class="required">*</span>' : '';

        return '<label for="' . htmlspecialchars($for, ENT_QUOTES, 'UTF-8') . '"' . $classAttr . '>' .
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . $requiredMark . '</label>';
    }
}
