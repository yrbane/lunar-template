<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate breadcrumb navigation with Schema.org markup.
 *
 * Usage:
 * - ##breadcrumbs(items)## - Array of [name, url] pairs
 * - ##breadcrumbs(items, " > ")## - Custom separator
 */
final class BreadcrumbsMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'breadcrumbs';
    }

    public function execute(array $args): string
    {
        $items = $args[0] ?? [];
        $separator = (string) ($args[1] ?? ' / ');
        $class = (string) ($args[2] ?? 'breadcrumbs');

        if (!\is_array($items) || $items === []) {
            return '';
        }

        $html = '<nav aria-label="Breadcrumb" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<ol itemscope itemtype="https://schema.org/BreadcrumbList">';

        $position = 1;
        $total = \count($items);

        foreach ($items as $item) {
            $name = \is_array($item) ? ($item['name'] ?? $item[0] ?? '') : (string) $item;
            $url = \is_array($item) ? ($item['url'] ?? $item[1] ?? null) : null;

            $html .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

            if ($url !== null && $position < $total) {
                $html .= '<a itemprop="item" href="' . htmlspecialchars((string) $url, ENT_QUOTES, 'UTF-8') . '">';
                $html .= '<span itemprop="name">' . htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8') . '</span>';
                $html .= '</a>';
            } else {
                $html .= '<span itemprop="name">' . htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8') . '</span>';
            }

            $html .= '<meta itemprop="position" content="' . $position . '">';
            $html .= '</li>';

            if ($position < $total) {
                $html .= '<span class="separator" aria-hidden="true">' . htmlspecialchars($separator, ENT_QUOTES, 'UTF-8') . '</span>';
            }

            $position++;
        }

        $html .= '</ol>';
        $html .= '</nav>';

        return $html;
    }
}
