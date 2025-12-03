<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class ChunkFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'chunk';
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $size = (int) ($args[0] ?? 1);
        $preserveKeys = (bool) ($args[1] ?? false);

        if ($size < 1) {
            $size = 1;
        }

        return array_chunk($value, $size, $preserveKeys);
    }
}
