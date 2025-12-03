<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\FilterInterface;

/**
 * Build a CSS class string from various inputs.
 *
 * Usage: [[ classes | class_list ]]
 *
 * Accepts:
 * - String: "foo bar" -> "foo bar"
 * - Array: ["foo", "bar"] -> "foo bar"
 * - Associative array: ["foo" => true, "bar" => false, "baz" => true] -> "foo baz"
 */
final class ClassListFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'class_list';
    }

    /**
     * @return string
     */
    public function apply(mixed $value, array $args = []): mixed
    {
        if (\is_string($value)) {
            return $this->normalizeString($value);
        }

        if (!\is_array($value)) {
            return '';
        }

        $classes = [];

        foreach ($value as $key => $item) {
            if (\is_int($key)) {
                // Indexed array: use value as class name if truthy
                if (\is_string($item) && $item !== '') {
                    $classes[] = $item;
                }
            } elseif (\is_string($key)) {
                // Associative array: use key as class name if value is truthy
                if ($item) {
                    $classes[] = $key;
                }
            }
        }

        return $this->normalizeString(implode(' ', $classes));
    }

    private function normalizeString(string $value): string
    {
        // Remove extra whitespace and trim
        $normalized = (string) preg_replace('/\s+/', ' ', trim($value));

        return htmlspecialchars($normalized, ENT_QUOTES, 'UTF-8');
    }
}
