<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

final class Nl2brFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'nl2br';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $useXhtml = (bool) ($args[0] ?? true);

        return nl2br($this->toString($value), $useXhtml);
    }
}
