<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Pluralize words based on count.
 *
 * Usage:
 * - ##pluralize(count, "item", "items")##
 * - ##pluralize(count, "child", "children")##
 * - ##pluralize(5, "fichier")## - Auto adds 's' in French style
 */
final class PluralizeMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'pluralize';
    }

    public function execute(array $args): string
    {
        $count = (int) ($args[0] ?? 0);
        $singular = (string) ($args[1] ?? '');
        $plural = (string) ($args[2] ?? $singular . 's');

        $word = abs($count) === 1 ? $singular : $plural;

        return $count . ' ' . $word;
    }
}
