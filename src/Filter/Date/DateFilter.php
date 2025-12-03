<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Date;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Lunar\Template\Filter\FilterInterface;

final class DateFilter implements FilterInterface
{
    public function __construct(
        private readonly string $defaultFormat = 'Y-m-d H:i:s',
        private readonly ?string $timezone = null,
    ) {
    }

    public function getName(): string
    {
        return 'date';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $format = (string) ($args[0] ?? $this->defaultFormat);

        $dateTime = $this->toDateTime($value);

        if ($dateTime === null) {
            return '';
        }

        if ($this->timezone !== null) {
            $dateTime = $dateTime->setTimezone(new DateTimeZone($this->timezone));
        }

        return $dateTime->format($format);
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
