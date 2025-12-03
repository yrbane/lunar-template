<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\FilterInterface;

/**
 * Convert an associative array to HTML attributes string.
 *
 * Usage: [[ attrs | attributes ]]
 * Input: ['class' => 'btn', 'id' => 'submit', 'disabled' => true]
 * Output: class="btn" id="submit" disabled
 */
final class AttributesFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'attributes';
    }

    /**
     * @return string
     */
    public function apply(mixed $value, array $args = []): mixed
    {
        if (!\is_array($value)) {
            return '';
        }

        if ($value === []) {
            return '';
        }

        $attributes = [];

        foreach ($value as $name => $attrValue) {
            $name = htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8');

            if ($attrValue === true) {
                // Boolean attribute (e.g., disabled, checked, readonly)
                $attributes[] = $name;
            } elseif ($attrValue === false || $attrValue === null) {
                // Skip false/null attributes
                continue;
            } elseif (\is_array($attrValue)) {
                // Array value - join with space (useful for classes)
                $attributes[] = $name . '="' . htmlspecialchars(implode(' ', $attrValue), ENT_QUOTES, 'UTF-8') . '"';
            } else {
                $attributes[] = $name . '="' . htmlspecialchars((string) $attrValue, ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        return implode(' ', $attributes);
    }
}
