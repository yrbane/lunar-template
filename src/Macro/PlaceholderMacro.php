<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate placeholder image URL.
 *
 * Usage:
 * - ##placeholder(300, 200)## - Gray placeholder
 * - ##placeholder(300, 200, "Text")## - With text
 * - ##placeholder(300, 200, "", "cccccc", "969696")## - Custom colors
 */
final class PlaceholderMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'placeholder';
    }

    public function execute(array $args): string
    {
        $width = (int) ($args[0] ?? 300);
        $height = (int) ($args[1] ?? 200);
        $text = (string) ($args[2] ?? '');
        $bgColor = (string) ($args[3] ?? 'cccccc');
        $textColor = (string) ($args[4] ?? '969696');

        $url = sprintf(
            'https://via.placeholder.com/%dx%d/%s/%s',
            $width,
            $height,
            $bgColor,
            $textColor
        );

        if ($text !== '') {
            $url .= '?text=' . urlencode($text);
        }

        return $url;
    }
}
