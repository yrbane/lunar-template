<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Alias for HeadingFilter - Create an HTML heading element.
 *
 * Usage: [[ title | h ]]      -> <h1>title</h1>
 * Usage: [[ title | h(2) ]]   -> <h2>title</h2>
 */
final class HFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'h';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $text = $this->toString($value);

        if ($text === '') {
            return '';
        }

        $level = (int) ($args[0] ?? 1);
        // Ensure level is between 1 and 6
        $level = max(1, min(6, $level));

        $class = $args[1] ?? null;
        $id = $args[2] ?? null;

        $attributes = '';
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }
        if ($id !== null && $id !== '') {
            $attributes .= ' id="' . htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<h' . $level . $attributes . '>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</h' . $level . '>';
    }
}
