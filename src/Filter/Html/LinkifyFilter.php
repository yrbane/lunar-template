<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Automatically convert URLs and email addresses to clickable links.
 */
final class LinkifyFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'linkify';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $text = $this->toString($value);

        if ($text === '') {
            return '';
        }

        $target = $this->getArg($args, 0, '_blank');
        $targetAttr = $target ? ' target="' . htmlspecialchars((string) $target, ENT_QUOTES, 'UTF-8') . '"' : '';
        $relAttr = $target === '_blank' ? ' rel="noopener noreferrer"' : '';

        // Convert URLs
        $text = (string) preg_replace_callback(
            '~(?<!href="|src="|">)(https?://[^\s<>"\']+)~i',
            fn (array $matches): string => '<a href="' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '"' . $targetAttr . $relAttr . '>' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</a>',
            $text,
        );

        // Convert email addresses
        $text = (string) preg_replace_callback(
            '~(?<!href="|mailto:)([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})~',
            fn (array $matches): string => '<a href="mailto:' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8') . '</a>',
            $text,
        );

        return $text;
    }
}
