<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in a <strong> element.
 *
 * Usage: [[ text | strong ]]            -> <strong>text</strong>
 * Usage: [[ text | strong("warning") ]] -> <strong class="warning">text</strong>
 */
final class StrongFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'strong';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $class = $args[0] ?? null;

        $attributes = '';
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<strong' . $attributes . '>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</strong>';
    }
}
