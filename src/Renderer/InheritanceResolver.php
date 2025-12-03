<?php

declare(strict_types=1);

namespace Lunar\Template\Renderer;

use Lunar\Template\Exception\CircularInheritanceException;
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Security\PathValidator;

/**
 * Resolves template inheritance chains.
 */
class InheritanceResolver
{
    public function __construct(
        private readonly PathValidator $pathValidator,
    ) {
    }

    /**
     * Resolve inheritance chain and merge blocks.
     *
     * @param string $source Template source
     * @param array<string> $chain Inheritance chain for circular detection
     *
     * @return string Resolved template source
     */
    public function resolve(string $source, array $chain = []): string
    {
        $isTopLevel = empty($chain);
        $result = $this->doResolve($source, $chain);

        // Only cleanup block tags at the top level
        if ($isTopLevel) {
            $result = $this->cleanupBlocks($result);
        }

        return $result;
    }

    /**
     * Internal resolve implementation.
     *
     * @param string $source Template source
     * @param array<string> $chain Inheritance chain for circular detection
     *
     * @return string Resolved template source (may still have block tags)
     */
    private function doResolve(string $source, array $chain): string
    {
        $extends = $this->parseExtends($source);

        if ($extends === null) {
            return $source;
        }

        // Check for circular inheritance
        if (\in_array($extends, $chain, true)) {
            $chain[] = $extends;

            throw new CircularInheritanceException($chain);
        }

        // Add to chain
        $chain[] = $extends;

        // Remove extends directive
        $source = (string) preg_replace('/\[%\s*extends\s+[\'"](.+?)[\'"]\s*%\]/', '', $source);

        // Extract child blocks
        $childBlocks = $this->extractBlocks($source);

        // Load parent template
        $parentFile = $this->pathValidator->validate($this->ensureExtension($extends));

        if (!file_exists($parentFile)) {
            throw TemplateException::parentTemplateNotFound($parentFile);
        }

        $parentSource = file_get_contents($parentFile);
        if ($parentSource === false) {
            // @codeCoverageIgnoreStart
            throw TemplateException::unableToReadTemplate($parentFile);
            // @codeCoverageIgnoreEnd
        }

        // Recursively resolve parent (keeping block tags)
        $parentSource = $this->doResolve($parentSource, $chain);

        // Merge blocks (preserving block structure)
        return $this->mergeBlocks($parentSource, $childBlocks);
    }

    /**
     * Parse extends directive.
     */
    private function parseExtends(string $source): ?string
    {
        if (preg_match('/\[%\s*extends\s+[\'"](.+?)[\'"]\s*%\]/', $source, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract blocks from template source.
     *
     * @return array<string, string>
     */
    private function extractBlocks(string $source): array
    {
        $blocks = [];

        if (preg_match_all('/\[%\s*block\s+(\w+)\s*%\](.*?)\[%\s*endblock\s*%\]/s', $source, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $blocks[$match[1]] = $match[2];
            }
        }

        return $blocks;
    }

    /**
     * Merge child blocks into parent template.
     *
     * @param string $parentSource Parent template source
     * @param array<string, string> $childBlocks Child block contents
     *
     * @return string Merged template
     */
    private function mergeBlocks(string $parentSource, array $childBlocks): string
    {
        return (string) preg_replace_callback(
            '/\[%\s*block\s+(\w+)\s*%\](.*?)\[%\s*endblock\s*%\]/s',
            function ($matches) use ($childBlocks) {
                $blockName = $matches[1];
                $parentContent = $matches[2];

                if (isset($childBlocks[$blockName])) {
                    $childContent = $childBlocks[$blockName];

                    // Handle [% parent %] directive - replace with parent's block content
                    if (str_contains($childContent, '[% parent %]')) {
                        $childContent = str_replace('[% parent %]', $parentContent, $childContent);
                    }

                    // Preserve block structure for further inheritance
                    return '[% block ' . $blockName . ' %]' . $childContent . '[% endblock %]';
                }

                // Keep the full block structure
                return $matches[0];
            },
            $parentSource,
        );
    }

    /**
     * Remove block tags from resolved template.
     */
    private function cleanupBlocks(string $source): string
    {
        return (string) preg_replace('/\[%\s*block\s+\w+\s*%\](.*?)\[%\s*endblock\s*%\]/s', '$1', $source);
    }

    /**
     * Ensure template has .tpl extension.
     */
    private function ensureExtension(string $template): string
    {
        if (!str_ends_with($template, '.tpl')) {
            return $template . '.tpl';
        }

        return $template;
    }
}
