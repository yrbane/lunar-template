<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\FilterInterface;

/**
 * Convert an array to an HTML list (ul or ol).
 */
final class ListHtmlFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'list';
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

        $type = (string) ($args[0] ?? 'ul');
        $class = isset($args[1]) ? ' class="' . htmlspecialchars((string) $args[1], ENT_QUOTES, 'UTF-8') . '"' : '';

        $tag = \in_array($type, ['ol', 'ul'], true) ? $type : 'ul';

        $items = array_map(
            fn (mixed $item): string => '<li>' . htmlspecialchars($this->itemToString($item), ENT_QUOTES, 'UTF-8') . '</li>',
            array_values($value),
        );

        return '<' . $tag . $class . '>' . implode('', $items) . '</' . $tag . '>';
    }

    private function itemToString(mixed $item): string
    {
        if (\is_string($item)) {
            return $item;
        }

        if (is_numeric($item)) {
            return (string) $item;
        }

        if (\is_bool($item)) {
            return $item ? 'true' : 'false';
        }

        if (\is_array($item)) {
            return implode(', ', array_map(fn ($v) => $this->itemToString($v), $item));
        }

        if (\is_object($item) && method_exists($item, '__toString')) {
            return (string) $item;
        }

        return '';
    }
}
