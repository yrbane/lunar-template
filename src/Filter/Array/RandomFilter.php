<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Array;

use Lunar\Template\Filter\FilterInterface;

final class RandomFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'random';
    }

    public function apply(mixed $value, array $args = []): mixed
    {
        if (\is_array($value)) {
            if (empty($value)) {
                return null;
            }

            return $value[array_rand($value)];
        }

        if (\is_string($value) && $value !== '') {
            $length = mb_strlen($value);

            return mb_substr($value, random_int(0, $length - 1), 1);
        }

        if (\is_int($value)) {
            $min = (int) ($args[0] ?? 0);

            return random_int($min, $value);
        }

        return null;
    }
}
