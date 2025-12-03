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
     * Compile variable output [[ expression ]].
     */
    private function compileVariables(string $source): string
    {
        return (string) preg_replace_callback('/\[\[\s*(.*?)\s*\]\]/', function ($matches) {
            $expression = trim($matches[1]);

            if ($expression === '') {
                return '';
            }

            $expression = $this->convertDotNotation($expression);

            return '<?= htmlspecialchars((string)(' . $expression . ' ?? \'\'), ENT_QUOTES, \'UTF-8\') ?>';
        }, $source);
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
