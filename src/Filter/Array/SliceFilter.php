<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class SliceFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'slice';
    }

    /**
     * @return array<mixed>|string
     */
    public function apply(mixed $value, array $args = []): array|string
    {
        $start = (int) ($args[0] ?? 0);
        $length = $args[1] ?? null;

        if (\is_array($value)) {
            if ($length !== null) {
                return \array_slice($value, $start, (int) $length);
            }

            return \array_slice($value, $start);
        }

        if (\is_string($value)) {
            if ($length !== null) {
                return mb_substr($value, $start, (int) $length);
            }

            return mb_substr($value, $start);
        }

        return [];
    }
}
