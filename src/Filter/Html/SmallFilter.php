<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in a <small> element.
 *
 * Usage: [[ text | small ]]           -> <small>text</small>
 * Usage: [[ text | small("muted") ]]  -> <small class="muted">text</small>
 */
final class SmallFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'small';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $class = $args[0] ?? null;

        $attributes = '';
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<small' . $attributes . '>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</small>';
    }
}
