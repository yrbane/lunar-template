<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Generate relative time with <time> element.
 *
 * Usage:
 * - ##timeago(timestamp)## - "5 minutes ago"
 * - ##timeago(date, "short")## - "5m"
 */
final class TimeAgoMacro implements MacroInterface
{
    private const array INTERVALS = [
        ['year', 31536000, 'y'],
        ['month', 2592000, 'mo'],
        ['week', 604800, 'w'],
        ['day', 86400, 'd'],
        ['hour', 3600, 'h'],
        ['minute', 60, 'm'],
        ['second', 1, 's'],
    ];

    public function getName(): string
    {
        return 'timeago';
    }

    public function execute(array $args): string
    {
        $value = $args[0] ?? null;
        $format = (string) ($args[1] ?? 'long');

        $dateTime = $this->parseValue($value);
        if ($dateTime === null) {
            return '';
        }

        $now = new DateTimeImmutable();
        $diff = $now->getTimestamp() - $dateTime->getTimestamp();
        $isFuture = $diff < 0;
        $diff = abs($diff);

        $text = $this->formatDiff($diff, $format, $isFuture);
        $iso = $dateTime->format('c');

        return '<time datetime="' . $iso . '" title="' . $dateTime->format('F j, Y H:i:s') . '">' . $text . '</time>';
    }

    private function formatDiff(int $seconds, string $format, bool $isFuture): string
    {
        if ($seconds < 5) {
            return $format === 'short' ? 'now' : 'just now';
        }

        foreach (self::INTERVALS as [$name, $interval, $short]) {
            $count = (int) floor($seconds / $interval);

            if ($count >= 1) {
                if ($format === 'short') {
                    $text = $count . $short;
                } else {
                    $text = $count . ' ' . $name . ($count > 1 ? 's' : '');
                }

                return $isFuture ? 'in ' . $text : $text . ' ago';
            }
        }

        return 'just now';
    }

    private function parseValue(mixed $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (\is_int($value)) {
            return (new DateTimeImmutable())->setTimestamp($value);
        }

        if (\is_string($value) && $value !== '') {
            try {
                return new DateTimeImmutable($value);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }
}
