<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in a <span> element.
 *
 * Usage: [[ text | span ]]              -> <span>text</span>
 * Usage: [[ text | span("highlight") ]] -> <span class="highlight">text</span>
 */
final class SpanFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'span';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $class = $args[0] ?? null;
        $id = $args[1] ?? null;

        $attributes = '';
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($id !== null && $id !== '') {
            $attributes .= ' id="' . htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<span' . $attributes . '>' . $content . '</span>';
    }
}
