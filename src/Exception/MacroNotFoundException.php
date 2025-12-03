<?php

declare(strict_types=1);

namespace Lunar\Template\Exception;

use Throwable;

/**
 * Exception thrown when a macro is called but not registered.
 */
class MacroNotFoundException extends TemplateException
{
    private string $macroName;

    public function __construct(string $macroName, ?Throwable $previous = null)
    {
        $this->macroName = $macroName;

        parent::__construct(
            \sprintf('Macro "%s" is not defined', $macroName),
            0,
            $previous,
        );
    }

    public function getMacroName(): string
    {
        return $this->macroName;
    }
}
