<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Alias for AnchorFilter - Create an HTML anchor/link element.
 *
 * Usage: [[ url | a ]]              -> <a href="url">url</a>
 * Usage: [[ url | a("Click") ]]     -> <a href="url">Click</a>
 */
final class AFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'a';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $url = $this->toString($value);

        if ($url === '') {
            return '';
        }

        $text = $args[0] ?? $url;
        $target = $args[1] ?? null;
        $class = $args[2] ?? null;

        $attributes = ' href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"';

        if ($target !== null && $target !== '') {
            $attributes .= ' target="' . htmlspecialchars((string) $target, ENT_QUOTES, 'UTF-8') . '"';
            if ($target === '_blank') {
                $attributes .= ' rel="noopener noreferrer"';
            }
        }

        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<a' . $attributes . '>' . htmlspecialchars($this->toString($text), ENT_QUOTES, 'UTF-8') . '</a>';
    }
}
