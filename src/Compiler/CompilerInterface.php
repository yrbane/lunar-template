<?php

declare(strict_types=1);

namespace Lunar\Template\Compiler;

/**
 * Interface for template compilers.
 */
interface CompilerInterface
{
    /**
     * Compile template source into executable PHP code.
     *
     * @param string $source Template source
     *
     * @return string Compiled PHP code
     */
    public function compile(string $source): string;
}
