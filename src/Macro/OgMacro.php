<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate Open Graph meta tags.
 *
 * Usage:
 * - ##og("title", "Page Title")##
 * - ##og("description", "Page description")##
 * - ##og("image", "https://example.com/image.jpg")##
 * - ##og("url", "https://example.com/page")##
 */
final class OgMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'og';
    }

    public function execute(array $args): string
    {
        $property = (string) ($args[0] ?? '');
        $content = (string) ($args[1] ?? '');

        if ($property === '' || $content === '') {
            return '';
        }

        $ogProperty = str_starts_with($property, 'og:') ? $property : 'og:' . $property;

        return '<meta property="' . htmlspecialchars($ogProperty, ENT_QUOTES, 'UTF-8') . '" content="' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '">';
    }
}
