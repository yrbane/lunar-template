<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in an HTML tag.
 *
 * Usage: [[ content | wrap("div", "my-class") ]]
 * Output: <div class="my-class">content</div>
 */
final class WrapFilter extends AbstractFilter
{
    private const array SELF_CLOSING = ['br', 'hr', 'img', 'input', 'meta', 'link'];

    public function getName(): string
    {
        return 'wrap';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $tag = (string) ($args[0] ?? 'div');
        $class = $args[1] ?? null;
        $id = $args[2] ?? null;

        // Sanitize tag name
        $tag = preg_replace('/[^a-zA-Z0-9]/', '', $tag);
        if ($tag === '' || $tag === null) {
            $tag = 'div';
        }

        // Don't wrap in self-closing tags
        if (\in_array(strtolower($tag), self::SELF_CLOSING, true)) {
            return $content;
        }

        $attributes = '';
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($id !== null && $id !== '') {
            $attributes .= ' id="' . htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<' . $tag . $attributes . '>' . $content . '</' . $tag . '>';
    }
}
