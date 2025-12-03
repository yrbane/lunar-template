<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate stylesheet link tag.
 *
 * Usage:
 * - ##style("/css/app.css")## - Basic stylesheet
 * - ##style("/css/print.css", "print")## - Print media
 * - ##style("/css/app.css", "all", true)## - Preload
 */
final class StyleMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'style';
    }

    public function execute(array $args): string
    {
        $href = (string) ($args[0] ?? '');
        $media = (string) ($args[1] ?? 'all');
        $preload = (bool) ($args[2] ?? false);

        if ($href === '') {
            return '';
        }

        if ($preload) {
            return '<link rel="preload" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' .
                '<noscript><link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        }

        return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" media="' . htmlspecialchars($media, ENT_QUOTES, 'UTF-8') . '">';
    }
}
