<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in an <abbr> element (abbreviation).
 *
 * Usage: [[ "HTML" | abbr("HyperText Markup Language") ]]
 * Output: <abbr title="HyperText Markup Language">HTML</abbr>
 */
final class AbbrFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'abbr';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $title = $args[0] ?? null;

        if ($title === null || $title === '') {
            return '<abbr>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</abbr>';
        }

        return '<abbr title="' . htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</abbr>';
    }
}
