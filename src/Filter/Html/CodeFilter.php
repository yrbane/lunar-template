<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Wrap content in a <code> element.
 *
 * Usage: [[ text | code ]]          -> <code>text</code>
 * Usage: [[ text | code("php") ]]   -> <code class="language-php">text</code>
 */
final class CodeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'code';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $language = $args[0] ?? null;

        $attributes = '';
        if ($language !== null && $language !== '') {
            $attributes .= ' class="language-' . htmlspecialchars((string) $language, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<code' . $attributes . '>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</code>';
    }
}
