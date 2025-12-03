<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in an <em> element (emphasis/italic).
 *
 * Usage: [[ text | em ]]          -> <em>text</em>
 * Usage: [[ text | em("note") ]]  -> <em class="note">text</em>
 */
final class EmFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'em';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $class = $args[0] ?? null;

        $attributes = '';
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<em' . $attributes . '>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</em>';
    }
}
