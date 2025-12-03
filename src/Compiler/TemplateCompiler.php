<?php

declare(strict_types=1);

namespace Lunar\Template\Compiler;

/**
 * Compiles Lunar template syntax into PHP code.
 */
class TemplateCompiler implements CompilerInterface
{
    /**
     * {@inheritDoc}
     */
    public function compile(string $source): string
    {
        // Process raw output [[! ... !]] (no escaping)
        $source = $this->compileRawOutput($source);

        // Process variables [[ ... ]]
        $source = $this->compileVariables($source);

        // Process conditions
        $source = $this->compileConditions($source);

        // Process loops
        $source = $this->compileLoops($source);

        // Process macros
        $source = $this->compileMacros($source);

        // Clean up remaining block tags
        $source = $this->cleanupBlockTags($source);

        return $source;
    }

    /**
     * Compile raw output [[! expression !]] without escaping.
     * Syntax: [[! variable !]] or [[! variable | filter !]]
     */
    private function compileRawOutput(string $source): string
    {
        return (string) preg_replace_callback('/\[\[!\s*(.*?)\s*!\]\]/', function ($matches) {
            $expression = trim($matches[1]);

            if ($expression === '') {
                return '';
            }

            // Check if there are filters
            $filters = $this->parseFilters($expression);
            $variable = $filters['variable'];
            $filterChain = $filters['filters'];

            $phpVar = $this->convertDotNotation($variable);

            // Build the filter chain
            if (empty($filterChain)) {
                return '<?= ' . $phpVar . ' ?? \'\' ?>';
            }

            // Apply filters
            $value = $phpVar . ' ?? \'\'';

            foreach ($filterChain as $filter) {
                $name = $filter['name'];
                $args = $filter['args'];
                $value = '$this->applyFilter(\'' . $name . '\', ' . $value . ', ' . $args . ')';
            }

            return '<?= ' . $value . ' ?>';
        }, $source);
    }

    /**
     * Compile variable output [[ expression ]] with optional filters.
     * Syntax: [[ variable ]] or [[ variable | filter ]] or [[ variable | filter(arg1, arg2) | filter2 ]]
     */
    private function compileVariables(string $source): string
    {
        return (string) preg_replace_callback('/\[\[\s*(.*?)\s*\]\]/', function ($matches) {
            $expression = trim($matches[1]);

            if ($expression === '') {
                return '';
            }

            // Check if there are filters
            $filters = $this->parseFilters($expression);
            $variable = $filters['variable'];
            $filterChain = $filters['filters'];

            $phpVar = $this->convertDotNotation($variable);

            // Build the filter chain
            if (empty($filterChain)) {
                return '<?= htmlspecialchars((string)(' . $phpVar . ' ?? \'\'), ENT_QUOTES, \'UTF-8\') ?>';
            }

            // Apply filters
            $value = $phpVar . ' ?? \'\'';

            foreach ($filterChain as $filter) {
                $name = $filter['name'];
                $args = $filter['args'];

                if ($name === 'raw') {
                    // Raw filter - no escaping at all
                    $value = '$this->applyFilter(\'' . $name . '\', ' . $value . ', ' . $args . ')';
                } else {
                    $value = '$this->applyFilter(\'' . $name . '\', ' . $value . ', ' . $args . ')';
                }
            }

            // Check if last filter is 'raw' - if so, don't escape
            $lastFilter = end($filterChain);
            if ($lastFilter !== false && $lastFilter['name'] === 'raw') {
                return '<?= ' . $value . ' ?>';
            }

            return '<?= htmlspecialchars((string)(' . $value . '), ENT_QUOTES, \'UTF-8\') ?>';
        }, $source);
    }

    /**
     * Parse variable expression and extract filters.
     *
     * @return array{variable: string, filters: array<int, array{name: string, args: string}>}
     */
    private function parseFilters(string $expression): array
    {
        $parts = $this->splitFilterPipe($expression);

        $variable = trim(array_shift($parts) ?? '');
        $filters = [];

        foreach ($parts as $filterExpr) {
            $filterExpr = trim($filterExpr);

            if ($filterExpr === '') {
                continue;
            }

            // Parse filter name and arguments: filter or filter(arg1, arg2)
            if (preg_match('/^(\w+)(?:\((.*)\))?$/', $filterExpr, $m)) {
                $name = $m[1];
                $args = isset($m[2]) ? $this->parseFilterArguments($m[2]) : '[]';
                $filters[] = ['name' => $name, 'args' => $args];
            }
        }

        return ['variable' => $variable, 'filters' => $filters];
    }

    /**
     * Split expression by pipe character, respecting strings and parentheses.
     *
     * @return array<int, string>
     */
    private function splitFilterPipe(string $expression): array
    {
        $parts = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';
        $parenDepth = 0;

        for ($i = 0; $i < mb_strlen($expression); $i++) {
            $char = $expression[$i];

            if (!$inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
                $current .= $char;
            } elseif ($inQuotes && $char === $quoteChar) {
                $inQuotes = false;
                $current .= $char;
            } elseif (!$inQuotes && $char === '(') {
                $parenDepth++;
                $current .= $char;
            } elseif (!$inQuotes && $char === ')') {
                $parenDepth--;
                $current .= $char;
            } elseif (!$inQuotes && $parenDepth === 0 && $char === '|') {
                $parts[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if ($current !== '') {
            $parts[] = $current;
        }

        return $parts;
    }

    /**
     * Parse filter arguments into PHP array syntax.
     */
    private function parseFilterArguments(string $args): string
    {
        if (trim($args) === '') {
            return '[]';
        }

        $arguments = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';
        $parenDepth = 0;
        $bracketDepth = 0;

        for ($i = 0; $i < mb_strlen($args); $i++) {
            $char = $args[$i];

            if (!$inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
                $current .= $char;
            } elseif ($inQuotes && $char === $quoteChar) {
                $inQuotes = false;
                $current .= $char;
            } elseif (!$inQuotes && $char === '(') {
                $parenDepth++;
                $current .= $char;
            } elseif (!$inQuotes && $char === ')') {
                $parenDepth--;
                $current .= $char;
            } elseif (!$inQuotes && ($char === '[' || $char === '{')) {
                $bracketDepth++;
                $current .= $char;
            } elseif (!$inQuotes && ($char === ']' || $char === '}')) {
                $bracketDepth--;
                $current .= $char;
            } elseif (!$inQuotes && $parenDepth === 0 && $bracketDepth === 0 && $char === ',') {
                $arguments[] = $this->convertFilterArgument(trim($current));
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current) !== '') {
            $arguments[] = $this->convertFilterArgument(trim($current));
        }

        return '[' . implode(', ', $arguments) . ']';
    }

    /**
     * Convert filter argument to PHP expression.
     */
    private function convertFilterArgument(string $arg): string
    {
        // String
        if (preg_match('/^(["\']).*\1$/', $arg)) {
            return $arg;
        }

        // Number
        if (is_numeric($arg)) {
            return $arg;
        }

        // Boolean or null
        $phpKeywords = ['true', 'false', 'null'];
        if (\in_array(strtolower($arg), $phpKeywords, true)) {
            return strtolower($arg);
        }

        // Array literal
        if (str_starts_with($arg, '[')) {
            return $arg;
        }

        // Variable
        return $this->convertDotNotation($arg);
    }

    /**
     * Compile conditional statements.
     */
    private function compileConditions(string $source): string
    {
        // if
        $source = (string) preg_replace_callback('/\[%\s*if\s+(.*?)\s*%\]/', function ($matches) {
            $condition = $this->processCondition($matches[1]);

            return '<?php if (' . $condition . '): ?>';
        }, $source);

        // elseif
        $source = (string) preg_replace_callback('/\[%\s*elseif\s+(.*?)\s*%\]/', function ($matches) {
            $condition = $this->processCondition($matches[1]);

            return '<?php elseif (' . $condition . '): ?>';
        }, $source);

        // else
        $source = (string) preg_replace('/\[%\s*else\s*%\]/', '<?php else: ?>', $source);

        // endif
        return (string) preg_replace('/\[%\s*endif\s*%\]/', '<?php endif; ?>', $source);
    }

    /**
     * Compile loop statements.
     */
    private function compileLoops(string $source): string
    {
        // for ... in ...
        $source = (string) preg_replace_callback('/\[%\s*for\s+(\S+)\s+in\s+(\S+)\s*%\]/', function ($matches) {
            $variable = ltrim($matches[1], '$');
            $arrayExpr = ltrim($matches[2], '$');
            $array = $this->addDollarToVariables($arrayExpr);

            return '<?php foreach((' . $array . ' ?? []) as $' . $variable . '): ?>';
        }, $source);

        // endfor
        return (string) preg_replace('/\[%\s*endfor\s*%\]/', '<?php endforeach; ?>', $source);
    }

    /**
     * Compile macro calls.
     */
    private function compileMacros(string $source): string
    {
        return (string) preg_replace_callback('/##(\w+)\((.*?)\)##/', function ($matches) {
            $macroName = $matches[1];
            $args = $matches[2];
            $parsedArgs = $this->parseMacroArguments($args);

            return '<?= $this->callMacro(\'' . $macroName . '\', ' . $parsedArgs . ') ?>';
        }, $source);
    }

    /**
     * Clean up remaining block tags.
     */
    private function cleanupBlockTags(string $source): string
    {
        $source = (string) preg_replace('/\[%\s*block\s+\S+\s*%\]/', '', $source);

        return (string) preg_replace('/\[%\s*endblock\s*%\]/', '', $source);
    }

    /**
     * Convert dot notation to array access.
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

    /**
     * Process a condition expression.
     */
    private function processCondition(string $condition): string
    {
        $condition = trim($condition);

        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $condition)) {
            $phpVar = $this->convertDotNotation($condition);

            return '!empty(' . $phpVar . ')';
        }

        return $this->addDollarToVariables($condition);
    }

    /**
     * Add dollar sign to variables in expressions.
     */
    private function addDollarToVariables(string $expression): string
    {
        $strings = [];
        $placeholder = '___STRING_PLACEHOLDER_%d___';
        $index = 0;

        $expression = (string) preg_replace_callback('/(["\'])(?:(?!\1)[^\\\\]|\\\\.)*\1/', function ($match) use (&$strings, &$index, $placeholder) {
            $key = \sprintf($placeholder, $index++);
            $strings[$key] = $match[0];

            return $key;
        }, $expression);

        $expression = (string) preg_replace_callback('/\b([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)\b/', function ($matches) {
            $var = $matches[1];

            // Only preserve PHP literals and logical operators, not function names
            $phpKeywords = ['true', 'false', 'null', 'and', 'or', 'not'];
            if (\in_array(strtolower($var), $phpKeywords, true)) {
                return $var;
            }

            if (str_contains($var, '___STRING_PLACEHOLDER_')) {
                return $var;
            }

            return $this->convertDotNotation($var);
        }, $expression);

        foreach ($strings as $key => $value) {
            $expression = str_replace($key, $value, $expression);
        }

        return $expression;
    }

    /**
     * Parse macro arguments.
     */
    private function parseMacroArguments(string $args): string
    {
        if (trim($args) === '') {
            return '[]';
        }

        $arguments = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';

        for ($i = 0; $i < \strlen($args); $i++) {
            $char = $args[$i];

            if (!$inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
                $current .= $char;
            } elseif ($inQuotes && $char === $quoteChar) {
                $inQuotes = false;
                $current .= $char;
            } elseif (!$inQuotes && $char === ',') {
                $arguments[] = $this->convertMacroArgument(trim($current));
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current) !== '') {
            $arguments[] = $this->convertMacroArgument(trim($current));
        }

        return '[' . implode(', ', $arguments) . ']';
    }

    /**
     * Convert macro argument to PHP expression.
     */
    private function convertMacroArgument(string $arg): string
    {
        if (preg_match('/^(["\']).*\1$/', $arg)) {
            return $arg;
        }

        if (is_numeric($arg)) {
            return $arg;
        }

        $phpKeywords = ['true', 'false', 'null'];
        if (\in_array(strtolower($arg), $phpKeywords, true)) {
            return $arg;
        }

        return $this->convertDotNotation($arg);
    }
}
