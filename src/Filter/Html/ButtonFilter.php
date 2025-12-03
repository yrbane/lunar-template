<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create a <button> element.
 *
 * Usage: [[ "Submit" | button ]]                  -> <button type="button">Submit</button>
 * Usage: [[ "Submit" | button("submit") ]]        -> <button type="submit">Submit</button>
 * Usage: [[ "Send" | button("submit", "btn") ]]   -> <button type="submit" class="btn">Send</button>
 */
final class ButtonFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'button';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $type = (string) ($args[0] ?? 'button');
        $class = $args[1] ?? null;
        $disabled = (bool) ($args[2] ?? false);

        // Validate type
        if (!\in_array($type, ['button', 'submit', 'reset'], true)) {
            $type = 'button';
        }

        $attributes = 'type="' . $type . '"';

        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($disabled) {
            $attributes .= ' disabled';
        }

        return '<button ' . $attributes . '>' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</button>';
    }
}
