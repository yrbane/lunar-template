<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Number;

use Lunar\Template\Filter\AbstractFilter;

final class FilesizeFilter extends AbstractFilter
{
    private const array UNITS = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

    public function getName(): string
    {
        return 'filesize';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $decimals = (int) $this->getArg($args, 0, 2);
        $bytes = (float) $value;

        if ($bytes <= 0) {
            return '0 B';
        }

        $unit = 0;
        while ($bytes >= 1024 && $unit < \count(self::UNITS) - 1) {
            $bytes /= 1024;
            $unit++;
        }

        return number_format($bytes, $decimals) . ' ' . self::UNITS[$unit];
    }
}
