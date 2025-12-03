<?php

declare(strict_types=1);

namespace Lunar\Template\Cache;

use Lunar\Template\Compiler\CompilerInterface;
use Lunar\Template\Compiler\TemplateCompiler;
use Lunar\Template\Renderer\InheritanceResolver;
use Lunar\Template\Security\PathValidator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Precompiles templates to warm the cache.
 */
class CacheWarmer
{
    private PathValidator $pathValidator;

    private CompilerInterface $compiler;

    private CacheInterface $cache;

    private InheritanceResolver $inheritanceResolver;

    public function __construct(
        string $templatePath,
        CacheInterface $cache,
        ?CompilerInterface $compiler = null,
    ) {
        $this->pathValidator = new PathValidator($templatePath);
        $this->cache = $cache;
        $this->compiler = $compiler ?? new TemplateCompiler();
        $this->inheritanceResolver = new InheritanceResolver($this->pathValidator);
    }

    /**
     * Warm the cache for a specific template.
     *
     * @param string $template Template name
     *
     * @return bool True if successfully warmed
     */
    public function warmTemplate(string $template): bool
    {
        $templateFile = $this->resolveTemplatePath($template);

        if (!file_exists($templateFile)) {
            return false;
        }

        $cacheKey = $this->getCacheKey($templateFile);

        $source = file_get_contents($templateFile);
        if ($source === false) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $source = $this->inheritanceResolver->resolve($source);
        $compiled = $this->compiler->compile($source);

        $this->cache->set($cacheKey, $compiled);

        return true;
    }

    /**
     * Warm all templates in a directory.
     *
     * @param string $pattern Glob pattern (default: *.tpl)
     *
     * @return array<string, bool> Results keyed by template name
     */
    public function warmDirectory(string $pattern = '*.tpl'): array
    {
        $basePath = $this->pathValidator->getBasePath();
        $files = glob($basePath . '/' . $pattern);
        $results = [];

        if ($files === false) {
            // @codeCoverageIgnoreStart
            return $results;
            // @codeCoverageIgnoreEnd
        }

        foreach ($files as $file) {
            $template = basename($file);
            $results[$template] = $this->warmTemplate($template);
        }

        return $results;
    }

    /**
     * Warm templates recursively.
     *
     * @return array<string, bool> Results keyed by template path
     */
    public function warmRecursive(): array
    {
        $basePath = $this->pathValidator->getBasePath();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $results = [];

        foreach ($iterator as $file) {
            // @codeCoverageIgnoreStart
            if (!$file instanceof SplFileInfo) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            if ($file->getExtension() !== 'tpl') {
                continue;
            }

            $relativePath = str_replace($basePath . '/', '', $file->getPathname());
            $results[$relativePath] = $this->warmTemplate($relativePath);
        }

        return $results;
    }

    /**
     * Resolve template path.
     */
    private function resolveTemplatePath(string $template): string
    {
        if (!str_ends_with($template, '.tpl')) {
            $template .= '.tpl';
        }

        return $this->pathValidator->validate($template);
    }

    /**
     * Generate cache key for template.
     */
    private function getCacheKey(string $templateFile): string
    {
        return md5($templateFile);
    }
}
