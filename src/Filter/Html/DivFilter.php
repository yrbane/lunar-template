<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in a <div> element.
 *
 * Usage: [[ content | div ]]                -> <div>content</div>
 * Usage: [[ content | div("container") ]]   -> <div class="container">content</div>
 * Usage: [[ content | div("box", "main") ]] -> <div class="box" id="main">content</div>
 */
final class DivFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'div';
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

        return '<div' . $attributes . '>' . $content . '</div>';
    }
}
