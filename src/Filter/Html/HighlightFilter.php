<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Highlight search terms in text with <mark> tags.
 *
 * Usage: [[ text | highlight("search term") ]]
 * Usage: [[ text | highlight("term", "highlight-class") ]]
 */
final class HighlightFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'highlight';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $text = $this->toString($value);

        if ($text === '') {
            return '';
        }

        $term = (string) ($args[0] ?? '');
        if ($term === '') {
            return $text;
        }

        $class = $args[1] ?? null;
        $tag = $class !== null && $class !== ''
            ? '<mark class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '">'
            : '<mark>';

        // Case-insensitive replacement while preserving original case
        $pattern = '/' . preg_quote($term, '/') . '/i';

        return (string) preg_replace_callback(
            $pattern,
            fn (array $matches): string => $tag . htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') . '</mark>',
            $text
        );
    }
}
