<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class JoinFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'join';
    }

    public function apply(mixed $value, array $args = []): string
    {
        if (!\is_array($value)) {
            return '';
        }

        $glue = (string) ($args[0] ?? ', ');
        $lastGlue = $args[1] ?? null;

        if ($lastGlue !== null && \count($value) > 1) {
            $last = array_pop($value);

            return implode($glue, $value) . (string) $lastGlue . $last;
        }

        return implode($glue, $value);
    }
}
