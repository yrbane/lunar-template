<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate canonical URL link.
 *
 * Usage:
 * - ##canonical("https://example.com/page")##
 */
final class CanonicalMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'canonical';
    }

    public function execute(array $args): string
    {
        $url = (string) ($args[0] ?? '');

        if ($url === '') {
            return '';
        }

        return '<link rel="canonical" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
    }
}
