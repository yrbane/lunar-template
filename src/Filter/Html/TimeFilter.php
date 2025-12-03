<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Lunar\Template\Filter\AbstractFilter;

/**
 * Create a <time> element with datetime attribute.
 *
 * Usage: [[ date | time ]]                  -> <time datetime="2025-01-15">2025-01-15</time>
 * Usage: [[ date | time("F j, Y") ]]        -> <time datetime="2025-01-15">January 15, 2025</time>
 */
final class TimeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'time';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $dateTime = $this->toDateTime($value);

        if ($dateTime === null) {
            return '';
        }

        $format = (string) ($args[0] ?? 'Y-m-d');
        $class = $args[1] ?? null;

        $datetime = $dateTime->format('c');
        $display = $dateTime->format($format);

        $attributes = 'datetime="' . htmlspecialchars($datetime, ENT_QUOTES, 'UTF-8') . '"';

        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<time ' . $attributes . '>' . htmlspecialchars($display, ENT_QUOTES, 'UTF-8') . '</time>';
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
