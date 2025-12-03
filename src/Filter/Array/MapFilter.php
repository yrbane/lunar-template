<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;
use Lunar\Template\Filter\FilterRegistry;

final class MapFilter implements FilterInterface
{
    public function __construct(
        private readonly FilterRegistry $registry,
    ) {
    }

    public function getName(): string
    {
        return 'map';
    }

    /**
     * @return array<mixed>
     */
    public function apply(mixed $value, array $args = []): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $filterName = (string) ($args[0] ?? '');

        if ($filterName === '' || !$this->registry->has($filterName)) {
            return $value;
        }

        $filterArgs = \array_slice($args, 1);

        return array_map(
            fn ($item) => $this->registry->apply($filterName, $item, $filterArgs),
            $value,
        );
    }
}
