<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate script tag.
 *
 * Usage:
 * - ##script("/js/app.js")## - Basic script
 * - ##script("/js/app.js", true)## - Async
 * - ##script("/js/app.js", false, true)## - Defer
 * - ##script("/js/app.js", false, false, "module")## - Module type
 */
final class ScriptMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'script';
    }

    public function execute(array $args): string
    {
        $src = (string) ($args[0] ?? '');
        $async = (bool) ($args[1] ?? false);
        $defer = (bool) ($args[2] ?? false);
        $type = (string) ($args[3] ?? '');

        if ($src === '') {
            return '';
        }

        $attributes = ['src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"'];

        if ($type !== '') {
            $attributes[] = 'type="' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($async) {
            $attributes[] = 'async';
        }

        if ($defer) {
            $attributes[] = 'defer';
        }

        return '<script ' . implode(' ', $attributes) . '></script>';
    }
}
