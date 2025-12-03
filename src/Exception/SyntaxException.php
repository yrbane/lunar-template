<?php

declare(strict_types=1);

namespace Lunar\Template\Exception;

use Throwable;

/**
 * Exception thrown when a template contains syntax errors.
 */
class SyntaxException extends TemplateException
{
    private int $lineNumber;

    private string $templatePath;

    public function __construct(
        string $message,
        string $templatePath = '',
        int $lineNumber = 0,
        ?Throwable $previous = null,
    ) {
        $this->lineNumber = $lineNumber;
        $this->templatePath = $templatePath;

        $fullMessage = $message;
        if ($templatePath !== '') {
            $fullMessage .= \sprintf(' in %s', $templatePath);
        }
        if ($lineNumber > 0) {
            $fullMessage .= \sprintf(' on line %d', $lineNumber);
        }

        parent::__construct($fullMessage, 0, $previous);
    }

    public function getLineNumber(): int
    {
        return $this->lineNumber;
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }
}
