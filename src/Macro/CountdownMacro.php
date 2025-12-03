<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;

/**
 * Generate countdown to a future date.
 *
 * Usage:
 * - ##countdown("2025-12-31")## - "28 days, 5 hours"
 * - ##countdown("2025-12-31", "full")## - Full breakdown
 * - ##countdown("2025-12-31", "days")## - Just days
 */
final class CountdownMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'countdown';
    }

    public function execute(array $args): string
    {
        $value = $args[0] ?? null;
        $format = (string) ($args[1] ?? 'default');

        $target = $this->parseValue($value);
        if ($target === null) {
            return '';
        }

        $now = new DateTimeImmutable();
        $diff = $target->getTimestamp() - $now->getTimestamp();

        if ($diff <= 0) {
            return 'Event passed';
        }

        return match ($format) {
            'full' => $this->formatFull($diff),
            'days' => (int) floor($diff / 86400) . ' days',
            'hours' => (int) floor($diff / 3600) . ' hours',
            default => $this->formatDefault($diff),
        };
    }

    private function formatDefault(int $seconds): string
    {
        $days = (int) floor($seconds / 86400);
        $hours = (int) floor(($seconds % 86400) / 3600);

        if ($days > 0) {
            return $days . ' day' . ($days > 1 ? 's' : '') . ', ' . $hours . ' hour' . ($hours > 1 ? 's' : '');
        }

        $minutes = (int) floor(($seconds % 3600) / 60);

        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ', ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
    }

    private function formatFull(int $seconds): string
    {
        $days = (int) floor($seconds / 86400);
        $hours = (int) floor(($seconds % 86400) / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . 'd';
        }
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }
        if ($secs > 0 || $parts === []) {
            $parts[] = $secs . 's';
        }

        return implode(' ', $parts);
    }

    private function parseValue(mixed $value): ?DateTimeImmutable
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
