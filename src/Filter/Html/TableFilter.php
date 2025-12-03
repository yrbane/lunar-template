<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\FilterInterface;

/**
 * Convert a 2D array to an HTML table.
 */
final class TableFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'table';
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

        $hasHeader = (bool) ($args[0] ?? false);
        $class = isset($args[1]) ? ' class="' . htmlspecialchars((string) $args[1], ENT_QUOTES, 'UTF-8') . '"' : '';

        $rows = array_values($value);
        $html = '<table' . $class . '>';

        foreach ($rows as $index => $row) {
            if (!\is_array($row)) {
                $row = [$row];
            }

            $isHeader = $hasHeader && $index === 0;
            $cellTag = $isHeader ? 'th' : 'td';
            $rowTag = $isHeader ? 'thead' : ($index === 1 && $hasHeader ? 'tbody' : '');

            if ($rowTag === 'thead') {
                $html .= '<thead>';
            } elseif ($rowTag === 'tbody') {
                $html .= '<tbody>';
            }

            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<' . $cellTag . '>' . htmlspecialchars($this->cellToString($cell), ENT_QUOTES, 'UTF-8') . '</' . $cellTag . '>';
            }
            $html .= '</tr>';

            if ($isHeader) {
                $html .= '</thead>';
            }
        }

        if ($hasHeader && \count($rows) > 1) {
            $html .= '</tbody>';
        }

        $html .= '</table>';

        return $html;
    }

    private function cellToString(mixed $cell): string
    {
        if (\is_string($cell)) {
            return $cell;
        }

        if (is_numeric($cell)) {
            return (string) $cell;
        }

        if (\is_bool($cell)) {
            return $cell ? 'true' : 'false';
        }

        if (\is_null($cell)) {
            return '';
        }

        if (\is_object($cell) && method_exists($cell, '__toString')) {
            return (string) $cell;
        }

        return '';
    }
}
