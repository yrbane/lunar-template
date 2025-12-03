<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate favicon link tags.
 *
 * Usage:
 * - ##favicon("/favicon.ico")## - Basic favicon
 * - ##favicon("/icon.png", "full")## - Full set with apple-touch-icon
 */
final class FaviconMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'favicon';
    }

    public function execute(array $args): string
    {
        $path = (string) ($args[0] ?? '/favicon.ico');
        $mode = (string) ($args[1] ?? 'basic');

        if ($mode === 'full') {
            $base = pathinfo($path, PATHINFO_DIRNAME);
            $base = $base === '.' ? '' : $base;

            return implode("\n", [
                '<link rel="icon" type="image/x-icon" href="' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '">',
                '<link rel="icon" type="image/png" sizes="32x32" href="' . $base . '/favicon-32x32.png">',
                '<link rel="icon" type="image/png" sizes="16x16" href="' . $base . '/favicon-16x16.png">',
                '<link rel="apple-touch-icon" sizes="180x180" href="' . $base . '/apple-touch-icon.png">',
            ]);
        }

        $type = str_ends_with($path, '.ico') ? 'image/x-icon' : 'image/png';

        return '<link rel="icon" type="' . $type . '" href="' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '">';
    }
}
