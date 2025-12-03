<?php

declare(strict_types=1);

namespace Lunar\Template\Compiler\Directive;

/**
 * Set directive for variable assignment.
 *
 * Usage: [% set variable = value %]
 */
class SetDirective implements DirectiveInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'set';
    }

    /**
     * {@inheritDoc}
     */
    public function compile(string $expression): string
    {
        // Parse "variable = value" format
        if (!preg_match('/^(\w+)\s*=\s*(.+)$/', trim($expression), $matches)) {
            return '';
        }

        $variable = $matches[1];
        $value = $this->convertValue(trim($matches[2]));

        return '<?php $' . $variable . ' = ' . $value . '; ?>';
    }

    /**
     * Convert template value to PHP expression.
     */
    private function convertValue(string $value): string
    {
        // If it's a quoted string, keep as-is
        if (preg_match('/^(["\']).*\1$/', $value)) {
            return $value;
        }

        // If it's a number, keep as-is
        if (is_numeric($value)) {
            return $value;
        }

        // If it's a boolean or null, keep as-is
        if (\in_array(strtolower($value), ['true', 'false', 'null'], true)) {
            return $value;
        }

        // If it's an array literal, keep as-is
        if (str_starts_with($value, '[') && str_ends_with($value, ']')) {
            return $value;
        }

        // Otherwise, treat as variable
        return $this->convertDotNotation($value);
    }

    /**
     * Convert dot notation to PHP array access.
     */
    private function convertDotNotation(string $expression): string
    {
        if (str_starts_with($expression, '$')) {
            $expression = substr($expression, 1);
        }

        $parts = explode('.', $expression);

        if (\count($parts) === 1) {
            return '$' . $parts[0];
        }

        $result = '$' . array_shift($parts);

        foreach ($parts as $part) {
            if (ctype_digit($part)) {
                $result .= '[' . $part . ']';
            } else {
                $result .= '[\'' . $part . '\']';
            }
        }

        return $result;
    }
}
