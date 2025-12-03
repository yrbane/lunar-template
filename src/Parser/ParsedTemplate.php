<?php

declare(strict_types=1);

namespace Lunar\Template\Parser;

/**
 * Represents a parsed template structure.
 */
class ParsedTemplate
{
    /**
     * @param string $source Original template source
     * @param array<string, string> $blocks Named blocks from the template
     * @param string|null $extends Parent template name if extending
     * @param array<string, array<int, mixed>> $macros Macro calls found in template
     */
    public function __construct(
        private readonly string $source,
        private readonly array $blocks = [],
        private readonly ?string $extends = null,
        private readonly array $macros = [],
    ) {
    }

    /**
     * Get original template source.
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Get named blocks.
     *
     * @return array<string, string>
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    /**
     * Get parent template name if extending.
     */
    public function getExtends(): ?string
    {
        return $this->extends;
    }

    /**
     * Check if template extends another.
     */
    public function hasParent(): bool
    {
        return $this->extends !== null;
    }

    /**
     * Get a specific block content.
     */
    public function getBlock(string $name): ?string
    {
        return $this->blocks[$name] ?? null;
    }

    /**
     * Check if template has a block.
     */
    public function hasBlock(string $name): bool
    {
        return isset($this->blocks[$name]);
    }

    /**
     * Get macro calls.
     *
     * @return array<string, array<int, mixed>>
     */
    public function getMacros(): array
    {
        return $this->macros;
    }
}
