<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create an HTML anchor/link element.
 *
 * Usage: [[ url | anchor ]]                    -> <a href="url">url</a>
 * Usage: [[ url | anchor("Click here") ]]      -> <a href="url">Click here</a>
 * Usage: [[ url | anchor("Link", "_blank") ]]  -> <a href="url" target="_blank" rel="noopener noreferrer">Link</a>
 */
final class AnchorFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'anchor';
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
