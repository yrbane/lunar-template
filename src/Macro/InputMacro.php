<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate HTML input element.
 *
 * Usage:
 * - ##input("email")## - Email input
 * - ##input("password", "password")## - Password input
 * - ##input("username", "text", "john")## - With value
 * - ##input("age", "number", "", "min=0 max=120")## - With attributes
 */
final class InputMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'input';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $type = (string) ($args[1] ?? 'text');
        $value = (string) ($args[2] ?? '');
        $attrs = (string) ($args[3] ?? '');
        $class = (string) ($args[4] ?? '');

        if ($name === '') {
            return '';
        }

        $attributes = [
            'type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"',
            'name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"',
            'id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"',
        ];

        if ($value !== '') {
            $attributes[] = 'value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($class !== '') {
            $attributes[] = 'class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($attrs !== '') {
            $attributes[] = $attrs;
        }

        return '<input ' . implode(' ', $attributes) . '>';
    }
}
