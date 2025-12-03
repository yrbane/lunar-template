<?php

declare(strict_types=1);

namespace Lunar\Template\Compiler\Directive;

/**
 * Interface for template directives.
 */
interface DirectiveInterface
{
    /**
     * Get the directive name (e.g., 'set', 'include').
     */
    public function getName(): string;

    /**
     * Compile the directive to PHP code.
     *
     * @param string $expression The expression following the directive name
     *
     * @return string Compiled PHP code
     */
    public function compile(string $expression): string;
}
