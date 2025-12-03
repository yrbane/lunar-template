<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Extract initials from name.
 *
 * Usage:
 * - ##initials("John Doe")## - JD
 * - ##initials("John Doe", 1)## - J
 * - ##initials("John Richard Doe", 3)## - JRD
 */
final class InitialsMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'initials';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $maxLength = (int) ($args[1] ?? 2);

        if ($name === '') {
            return '';
        }

        $words = preg_split('/[\s\-_]+/', trim($name));

        if ($words === false || $words === []) {
            return '';
        }

        $initials = array_map(
            fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)),
            $words,
        );

        return implode('', \array_slice($initials, 0, $maxLength));
    }
}
