<?php

declare(strict_types=1);

namespace Lunar\Template\Parser;

/**
 * Parses Lunar template syntax into structured components.
 *
 * Supports:
 * - Variables: [[ variable ]]
 * - Blocks: [% block name %] ... [% endblock %]
 * - Extends: [% extends 'parent.tpl' %]
 * - Macros: ##macroName(args)##
 */
class TemplateParser implements ParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $source): ParsedTemplate
    {
        $extends = $this->parseExtends($source);
        $blocks = $this->parseBlocks($source);
        $macros = $this->parseMacros($source);

        return new ParsedTemplate($source, $blocks, $extends, $macros);
    }

    /**
     * Parse extends directive.
     */
    private function parseExtends(string $source): ?string
    {
        if (preg_match('/\[%\s*extends\s+[\'"](.+?)[\'"]\s*%\]/', $source, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Parse named blocks.
     *
     * @return array<string, string>
     */
    private function parseBlocks(string $source): array
    {
        $blocks = [];

        if (preg_match_all('/\[%\s*block\s+(\w+)\s*%\](.*?)\[%\s*endblock\s*%\]/s', $source, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $blocks[$match[1]] = $match[2];
            }
        }

        return $blocks;
    }

    /**
     * Parse macro calls.
     *
     * @return array<string, array<int, array<int, string>>>
     */
    private function parseMacros(string $source): array
    {
        $macros = [];

        if (preg_match_all('/##(\w+)\((.*?)\)##/', $source, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1];
                $args = $this->parseArguments($match[2]);

                if (!isset($macros[$name])) {
                    $macros[$name] = [];
                }
                $macros[$name][] = $args;
            }
        }

        return $macros;
    }

    /**
     * Parse macro arguments.
     *
     * @return array<int, string>
     */
    private function parseArguments(string $argsString): array
    {
        if (trim($argsString) === '') {
            return [];
        }

        $args = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';

        for ($i = 0; $i < \strlen($argsString); $i++) {
            $char = $argsString[$i];

            if (!$inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
                $current .= $char;
            } elseif ($inQuotes && $char === $quoteChar) {
                $inQuotes = false;
                $current .= $char;
            } elseif (!$inQuotes && $char === ',') {
                $args[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current) !== '') {
            $args[] = trim($current);
        }

        return $args;
    }
}
