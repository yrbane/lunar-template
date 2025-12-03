<?php

declare(strict_types=1);

namespace Lunar\Template\Compiler\Directive;

/**
 * Include directive for template inclusion.
 *
 * Usage: [% include 'template.tpl' %]
 * With variables: [% include 'template.tpl' with {key: value} %]
 */
class IncludeDirective implements DirectiveInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'include';
    }

    /**
     * {@inheritDoc}
     */
    public function compile(string $expression): string
    {
        $expression = trim($expression);

        // Parse "template" or "template with {variables}"
        if (preg_match('/^[\'"](.+?)[\'"]\s*(?:with\s+(.+))?$/i', $expression, $matches)) {
            $template = $matches[1];
            $variables = isset($matches[2]) ? $this->parseVariables($matches[2]) : '[]';

            return '<?= $this->renderInclude(\'' . $template . '\', array_merge(get_defined_vars(), ' . $variables . ')) ?>';
        }

        // Template as variable
        if (preg_match('/^(\$?\w+(?:\.\w+)*)\s*(?:with\s+(.+))?$/i', $expression, $matches)) {
            $template = $this->convertToVariable($matches[1]);
            $variables = isset($matches[2]) ? $this->parseVariables($matches[2]) : '[]';

            return '<?= $this->renderInclude(' . $template . ', array_merge(get_defined_vars(), ' . $variables . ')) ?>';
        }

        return '';
    }

    /**
     * Parse variables expression.
     */
    private function parseVariables(string $expression): string
    {
        $expression = trim($expression);

        // Handle JSON-like object syntax {key: value}
        if (str_starts_with($expression, '{') && str_ends_with($expression, '}')) {
            // Convert {key: value} to ['key' => value]
            $inner = substr($expression, 1, -1);

            $pairs = [];
            $current = '';
            $depth = 0;
            $inQuotes = false;
            $quoteChar = '';

            for ($i = 0; $i < \strlen($inner); $i++) {
                $char = $inner[$i];

                if (!$inQuotes && ($char === '"' || $char === "'")) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($inQuotes && $char === $quoteChar) {
                    $inQuotes = false;
                }

                if (!$inQuotes) {
                    if ($char === '{' || $char === '[') {
                        $depth++;
                    } elseif ($char === '}' || $char === ']') {
                        $depth--;
                    }

                    if ($char === ',' && $depth === 0) {
                        $pairs[] = $this->parsePair(trim($current));
                        $current = '';

                        continue;
                    }
                }

                $current .= $char;
            }

            if (trim($current) !== '') {
                $pairs[] = $this->parsePair(trim($current));
            }

            return '[' . implode(', ', $pairs) . ']';
        }

        return $expression;
    }

    /**
     * Parse a key: value pair.
     */
    private function parsePair(string $pair): string
    {
        if (preg_match('/^(\w+)\s*:\s*(.+)$/', $pair, $matches)) {
            $key = $matches[1];
            $value = trim($matches[2]);

            // Keep array/object literals as-is
            if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
                return '\'' . $key . '\' => ' . $value;
            }

            // Keep quoted strings, numbers, booleans, and null as-is
            if (preg_match('/^(["\']).*\1$/', $value) || is_numeric($value)) {
                return '\'' . $key . '\' => ' . $value;
            }

            if (\in_array(strtolower($value), ['true', 'false', 'null'], true)) {
                return '\'' . $key . '\' => ' . $value;
            }

            // Convert variable references
            return '\'' . $key . '\' => ' . $this->convertToVariable($value);
        }

        return $pair;
    }

    /**
     * Convert expression to PHP variable.
     */
    private function convertToVariable(string $expression): string
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
