<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create a <progress> element.
 *
 * Usage: [[ 75 | progress ]]              -> <progress value="75" max="100"></progress>
 * Usage: [[ 30 | progress(50) ]]          -> <progress value="30" max="50"></progress>
 * Usage: [[ 75 | progress(100, "bar") ]]  -> <progress value="75" max="100" class="bar"></progress>
 */
final class ProgressFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'progress';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $val = is_numeric($value) ? (float) $value : 0;
        $max = is_numeric($args[0] ?? null) ? (float) $args[0] : 100;
        $class = $args[1] ?? null;

        $attributes = 'value="' . $val . '" max="' . $max . '"';

        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<progress ' . $attributes . '></progress>';
    }
}
