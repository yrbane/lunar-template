<?php

declare(strict_types=1);

namespace Lunar\Template\Parser;

/**
 * Interface for template parsers.
 */
interface ParserInterface
{
    /**
     * Parse template source and extract its structure.
     *
     * @param string $source Template source code
     *
     * @return ParsedTemplate Parsed template structure
     */
    public function parse(string $source): ParsedTemplate;
}
