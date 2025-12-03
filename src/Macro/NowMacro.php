<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Current timestamp/datetime.
 *
 * Usage:
 * - ##now()## - Unix timestamp
 * - ##now("Y-m-d")## - Formatted date
 * - ##now("iso")## - ISO 8601 format
 * - ##now("rfc")## - RFC 2822 format
 * - ##now("atom")## - Atom format
 */
final class NowMacro implements MacroInterface
{
    public function __construct(
        private readonly string $timezone = 'UTC',
    ) {
    }

    public function getName(): string
    {
        return 'now';
    }

    public function execute(array $args): string|int
    {
        $format = $args[0] ?? null;

        if ($format === null) {
            return time();
        }

        $dt = new DateTimeImmutable('now', new DateTimeZone($this->timezone));

        return match ($format) {
            'iso' => $dt->format('c'),
            'rfc' => $dt->format('r'),
            'atom' => $dt->format(\DATE_ATOM),
            'timestamp' => (string) $dt->getTimestamp(),
            default => $dt->format((string) $format),
        };
    }
}
