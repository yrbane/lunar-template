<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate HTML textarea element.
 *
 * Usage:
 * - ##textarea("message")## - Basic textarea
 * - ##textarea("bio", "Hello world")## - With value
 * - ##textarea("description", "", 5, 40)## - With rows/cols
 */
final class TextareaMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'textarea';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $value = (string) ($args[1] ?? '');
        $rows = (int) ($args[2] ?? 4);
        $cols = (int) ($args[3] ?? 50);
        $class = (string) ($args[4] ?? '');

        if ($name === '') {
            return '';
        }

        $attributes = [
            'name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"',
            'id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"',
            'rows="' . $rows . '"',
            'cols="' . $cols . '"',
        ];

        if ($class !== '') {
            $attributes[] = 'class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<textarea ' . implode(' ', $attributes) . '>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</textarea>';
    }
}
