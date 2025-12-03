<?php

declare(strict_types=1);

namespace Lunar\Template\Renderer;

/**
 * Interface for template renderers.
 */
interface RendererInterface
{
    /**
     * Render a template with given variables.
     *
     * @param string $template Template name or path
     * @param array<string, mixed> $variables Variables to inject
     *
     * @return string Rendered content
     */
    public function render(string $template, array $variables = []): string;

    /**
     * Check if a template exists.
     *
     * @param string $template Template name or path
     *
     * @return bool True if template exists
     */
    public function exists(string $template): bool;
}
