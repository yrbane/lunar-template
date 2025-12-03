<?php

declare(strict_types=1);

namespace Lunar\Template\Exception;

use Throwable;

/**
 * Exception thrown when a template file cannot be found.
 */
class TemplateNotFoundException extends TemplateException
{
    public function __construct(string $templatePath, ?Throwable $previous = null)
    {
        parent::__construct(
            \sprintf('Template not found: %s', $templatePath),
            0,
            $previous,
        );
    }
}
