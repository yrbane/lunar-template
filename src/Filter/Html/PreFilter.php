<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in a <pre> element (preformatted text).
 *
 * Usage: [[ text | pre ]]           -> <pre>text</pre>
 * Usage: [[ text | pre("code") ]]   -> <pre class="code">text</pre>
 */
final class PreFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'pre';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $class = $args[0] ?? null;

        $attributes = '';
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<pre' . $attributes . '>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</pre>';
    }
}
