<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * Date formatting macro.
 *
 * Usage:
 * - ##date()## - Current date in Y-m-d format
 * - ##date("F j, Y")## - Current date with custom format
 * - ##date("Y-m-d", timestamp)## - Format timestamp
 * - ##date("Y-m-d", dateTime)## - Format DateTime object
 */
class DateMacro implements MacroInterface
{
    private string $defaultFormat;

    private string $timezone;

    public function __construct(
        string $defaultFormat = 'Y-m-d',
        string $timezone = 'UTC',
    ) {
        $this->defaultFormat = $defaultFormat;
        $this->timezone = $timezone;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'date';
    }

    /**
     * {@inheritDoc}
     *
     * @param array<int, mixed> $args
     */
    public function execute(array $args): string
    {
        $format = $args[0] ?? $this->defaultFormat;
        $value = $args[1] ?? null;

        if (!\is_string($format)) {
            $format = $this->defaultFormat;
        }

        $dateTime = $this->parseValue($value);

        return $dateTime->format($format);
    }

    /**
     * Parse value into DateTimeImmutable.
     */
    private function parseValue(mixed $value): DateTimeImmutable
    {
        if ($value === null) {
            return new DateTimeImmutable('now', new DateTimeZone($this->timezone));
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (\is_int($value)) {
            return (new DateTimeImmutable('now', new DateTimeZone($this->timezone)))
                ->setTimestamp($value);
        }

        if (\is_string($value)) {
            return new DateTimeImmutable($value, new DateTimeZone($this->timezone));
        }

        return new DateTimeImmutable('now', new DateTimeZone($this->timezone));
    }
}
