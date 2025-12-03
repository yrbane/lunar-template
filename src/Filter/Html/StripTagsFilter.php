<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

final class StripTagsFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'striptags';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $allowedTags = $args[0] ?? null;

        $str = $this->toString($value);

        if (\is_string($allowedTags)) {
            return strip_tags($str, $allowedTags);
        }

        if (\is_array($allowedTags)) {
            $tags = implode('', array_map(fn ($t) => "<$t>", $allowedTags));

            return strip_tags($str, $tags);
        }

        return strip_tags($str);
    }
}
