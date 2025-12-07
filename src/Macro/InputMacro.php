<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

use Lunar\Template\Html\AttributeBag;

/**
 * Generate HTML input element.
 *
 * Usage:
 * - ##input("email")## - Email input
 * - ##input("password", "password")## - Password input
 * - ##input("username", "text", "john")## - With value
 * - ##input("age", "number", "", ["min" => 0, "max" => 120])## - With secure attributes
 * - ##input("age", "number", "", "min=0 max=120")## - With legacy attributes (raw string)
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
        $extra = $args[3] ?? [];
        $class = (string) ($args[4] ?? '');

        if ($name === '') {
            return '';
        }

        $bag = new AttributeBag([
            'type' => $type,
            'name' => $name,
            'id' => $name,
        ]);

        if ($value !== '') {
            $bag->add('value', $value);
        }

        if ($class !== '') {
            $bag->add('class', $class);
        }

        $legacyString = '';
        if (is_array($extra)) {
            foreach ($extra as $key => $val) {
                $bag->add((string) $key, $val);
            }
        } elseif (is_string($extra) && $extra !== '') {
            $legacyString = ' ' . $extra;
        }

        return '<input ' . $bag . $legacyString . '>';
    }
}
