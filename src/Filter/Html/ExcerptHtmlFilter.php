<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Strip HTML tags and create a clean excerpt.
 *
 * Usage: [[ htmlContent | excerpt_html ]]
 * Usage: [[ htmlContent | excerpt_html(200, "...") ]]
 */
final class ExcerptHtmlFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'excerpt_html';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $html = $this->toString($value);

        if ($html === '') {
            return '';
        }

        $length = (int) ($args[0] ?? 150);
        $suffix = (string) ($args[1] ?? '...');

        // Strip HTML tags
        $text = strip_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Normalize whitespace
        $text = (string) preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text) <= $length) {
            return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        }

        // Truncate at word boundary
        $text = mb_substr($text, 0, $length);
        $lastSpace = mb_strrpos($text, ' ');

        if ($lastSpace !== false && $lastSpace > $length * 0.8) {
            $text = mb_substr($text, 0, $lastSpace);
        }

        return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8') . $suffix;
    }
}
