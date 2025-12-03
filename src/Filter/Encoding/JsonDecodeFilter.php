<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Encoding;

use JsonException;
use Lunar\Template\Filter\AbstractFilter;

final class JsonDecodeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'json_decode';
    }

    /**
     * @return array<mixed>|null
     */
    public function apply(mixed $value, array $args = []): ?array
    {
        $str = $this->toString($value);

        if ($str === '') {
            return null;
        }

        try {
            $decoded = json_decode($str, true, 512, JSON_THROW_ON_ERROR);

            return \is_array($decoded) ? $decoded : null;
        } catch (JsonException) {
            return null;
        }
    }
}
