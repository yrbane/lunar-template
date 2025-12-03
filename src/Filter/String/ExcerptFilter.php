<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class ExcerptFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'excerpt';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $str = $this->toString($value);
        $length = (int) $this->getArg($args, 0, 100);
        $suffix = (string) $this->getArg($args, 1, '...');

        // Strip HTML tags
        $str = strip_tags($str);

        // Normalize whitespace
        $str = (string) preg_replace('/\s+/', ' ', $str);
        $str = trim($str);

        if (mb_strlen($str) <= $length) {
            return $str;
        }

        // Find last space within length
        $truncated = mb_substr($str, 0, $length);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > $length * 0.8) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return $truncated . $suffix;
    }
}
