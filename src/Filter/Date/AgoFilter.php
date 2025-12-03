<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Date;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Lunar\Template\Filter\FilterInterface;

final class AgoFilter implements FilterInterface
{
    private const array INTERVALS = [
        ['year', 31536000],
        ['month', 2592000],
        ['week', 604800],
        ['day', 86400],
        ['hour', 3600],
        ['minute', 60],
        ['second', 1],
    ];

    public function getName(): string
    {
        return 'ago';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $dateTime = $this->toDateTime($value);

        if ($dateTime === null) {
            return '';
        }

        $now = new DateTimeImmutable();
        $diff = $now->getTimestamp() - $dateTime->getTimestamp();

        if ($diff < 0) {
            return $this->formatFuture(abs($diff));
        }

        return $this->formatPast($diff);
    }

    private function formatPast(int $seconds): string
    {
        if ($seconds < 5) {
            return 'just now';
        }

        foreach (self::INTERVALS as [$name, $interval]) {
            $count = (int) floor($seconds / $interval);

            if ($count >= 1) {
                return $count . ' ' . $name . ($count > 1 ? 's' : '') . ' ago';
            }
        }

        return 'just now';
    }

    private function formatFuture(int $seconds): string
    {
        foreach (self::INTERVALS as [$name, $interval]) {
            $count = (int) floor($seconds / $interval);

            if ($count >= 1) {
                return 'in ' . $count . ' ' . $name . ($count > 1 ? 's' : '');
            }
        }

        return 'in a moment';
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
