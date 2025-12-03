<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create a <meter> element.
 *
 * Usage: [[ 0.6 | meter ]]                  -> <meter value="0.6"></meter>
 * Usage: [[ 75 | meter(0, 100) ]]           -> <meter value="75" min="0" max="100"></meter>
 * Usage: [[ 75 | meter(0, 100, 50, 80) ]]   -> <meter value="75" min="0" max="100" low="50" high="80"></meter>
 */
final class MeterFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'meter';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $val = is_numeric($value) ? (float) $value : 0;

        $attributes = 'value="' . $val . '"';

        if (isset($args[0]) && is_numeric($args[0])) {
            $attributes .= ' min="' . (float) $args[0] . '"';
        }

        if (isset($args[1]) && is_numeric($args[1])) {
            $attributes .= ' max="' . (float) $args[1] . '"';
        }

        if (isset($args[2]) && is_numeric($args[2])) {
            $attributes .= ' low="' . (float) $args[2] . '"';
        }

        if (isset($args[3]) && is_numeric($args[3])) {
            $attributes .= ' high="' . (float) $args[3] . '"';
        }

        $class = $args[4] ?? null;
        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<meter ' . $attributes . '></meter>';
    }
}
