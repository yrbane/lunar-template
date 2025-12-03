<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Raw filter - marks content as safe HTML (no escaping).
 * Use with caution - only for trusted content!
 */
final class RawFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'raw';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return $this->toString($value);
    }
}
