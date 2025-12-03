<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Date;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Lunar\Template\Filter\FilterInterface;

final class RelativeDateFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'relative';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $dateTime = $this->toDateTime($value);

        if ($dateTime === null) {
            return '';
        }

        $now = new DateTimeImmutable('today');
        $target = new DateTimeImmutable($dateTime->format('Y-m-d'));

        $diff = (int) $now->diff($target)->format('%r%a');

        return match (true) {
            $diff === 0 => 'today',
            $diff === 1 => 'tomorrow',
            $diff === -1 => 'yesterday',
            $diff > 1 && $diff <= 7 => 'in ' . $diff . ' days',
            $diff < -1 && $diff >= -7 => abs($diff) . ' days ago',
            $diff > 7 => $dateTime->format('M j, Y'),
            default => $dateTime->format('M j, Y'),
        };
    }

    private function toDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (\is_int($value)) {
            return (new DateTimeImmutable())->setTimestamp($value);
        }

        if (\is_string($value) && $value !== '') {
            try {
                return new DateTimeImmutable($value);
            } catch (Exception) {
                return null;
            }
        }

        return null;
    }
}
