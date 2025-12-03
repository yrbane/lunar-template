<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate meta tag.
 *
 * Usage:
 * - ##meta("description", "Page description")##
 * - ##meta("viewport", "width=device-width, initial-scale=1")##
 * - ##meta("robots", "index, follow")##
 * - ##meta("og:title", "Title", "property")## - Open Graph
 */
final class MetaMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'meta';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $content = (string) ($args[1] ?? '');
        $type = (string) ($args[2] ?? 'name');

        if ($name === '') {
            return '';
        }

        $nameAttr = $type === 'property' ? 'property' : 'name';

        return '<meta ' . $nameAttr . '="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" content="' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '">';
    }
}
