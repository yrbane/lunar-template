<?php

declare(strict_types=1);

namespace Lunar\Template\Html;

use Stringable;

/**
 * Helper to generate HTML attributes securely.
 */
class AttributeBag implements Stringable
{
    /**
     * @param array<string, string|bool|int|float|null> $attributes
     */
    public function __construct(
        private array $attributes = [],
    ) {
    }

    /**
     * Add or replace an attribute.
     */
    public function add(string $name, string|bool|int|float|null $value): self
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Get an attribute value.
     */
    public function get(string $name): string|bool|int|float|null
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Render attributes as HTML string.
     */
    public function __toString(): string
    {
        $html = [];

        foreach ($this->attributes as $key => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            // Boolean attributes (e.g. required)
            if ($value === true) {
                $html[] = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
                continue;
            }

            $safeKey = htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8');
            $safeValue = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

            $html[] = sprintf('%s="%s"', $safeKey, $safeValue);
        }

        return implode(' ', $html);
    }
}
