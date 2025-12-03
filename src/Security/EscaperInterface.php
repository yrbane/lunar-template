<?php

declare(strict_types=1);

namespace Lunar\Template\Security;

/**
 * Interface for escaping output in templates.
 */
interface EscaperInterface
{
    /**
     * Escape a value for safe output.
     *
     * @param mixed $value Value to escape
     *
     * @return string Escaped value
     */
    public function escape(mixed $value): string;

    /**
     * Get the escaping strategy name.
     *
     * @return string Strategy name (e.g., 'html', 'js', 'css')
     */
    public function getStrategy(): string;
}
