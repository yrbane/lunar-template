<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use Lunar\Template\Filter\AbstractFilter;

final class Sha256Filter extends AbstractFilter
{
    public function getName(): string
    {
        return 'sha256';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return hash('sha256', $this->toString($value));
    }
}
