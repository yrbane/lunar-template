<?php

declare(strict_types=1);

namespace Lunar\Template\Exception;

use Throwable;

/**
 * Exception thrown when circular template inheritance is detected.
 */
class CircularInheritanceException extends TemplateException
{
    /** @var array<string> */
    private array $inheritanceChain;

    /**
     * @param array<string> $inheritanceChain
     */
    public function __construct(array $inheritanceChain, ?Throwable $previous = null)
    {
        $this->inheritanceChain = $inheritanceChain;

        parent::__construct(
            \sprintf(
                'Circular template inheritance detected: %s',
                implode(' -> ', $inheritanceChain),
            ),
            0,
            $previous,
        );
    }

    /**
     * @return array<string>
     */
    public function getInheritanceChain(): array
    {
        return $this->inheritanceChain;
    }
}
