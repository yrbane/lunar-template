<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Convert text with double newlines to HTML paragraphs.
 *
 * Usage: [[ text | paragraph ]]
 */
final class ParagraphFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'paragraph';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $text = $this->toString($value);

        if ($text === '') {
            return '';
        }

        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Split on double newlines
        $paragraphs = preg_split('/\n{2,}/', $text);

        if ($paragraphs === false || $paragraphs === []) {
            return '';
        }

        // Wrap each non-empty paragraph in <p> tags
        $result = array_map(
            function (string $p): string {
                $p = trim($p);
                if ($p === '') {
                    return '';
                }

                // Convert single newlines to <br>
                $p = nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8'), false);

                return '<p>' . $p . '</p>';
            },
            $paragraphs
        );

        return implode("\n", array_filter($result));
    }
}
