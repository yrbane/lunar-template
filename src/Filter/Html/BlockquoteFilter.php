<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in a <blockquote> element.
 *
 * Usage: [[ text | blockquote ]]                -> <blockquote>text</blockquote>
 * Usage: [[ text | blockquote("Author") ]]      -> <blockquote>text<footer>Author</footer></blockquote>
 * Usage: [[ text | blockquote("Author", "quote") ]] -> <blockquote class="quote">text<footer>Author</footer></blockquote>
 */
final class BlockquoteFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'blockquote';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $cite = $args[0] ?? null;
        $class = $args[1] ?? null;

        $attributes = '';
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        $footer = '';
        if ($cite !== null && $cite !== '') {
            $footer = '<footer>' . htmlspecialchars((string) $cite, ENT_QUOTES, 'UTF-8') . '</footer>';
        }

        return '<blockquote' . $attributes . '>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . $footer . '</blockquote>';
    }
}
