<?php

declare(strict_types=1);

namespace Lunar\Template\Renderer;

use Lunar\Template\Compiler\CompilerInterface;
use Lunar\Template\Compiler\TemplateCompiler;
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Exception\TemplateNotFoundException;
use Lunar\Template\Filter\DefaultFilters;
use Lunar\Template\Filter\FilterInterface;
use Lunar\Template\Filter\FilterRegistry;
use Lunar\Template\Macro\MacroInterface;
use Lunar\Template\Security\PathValidator;
use Throwable;

/**
 * Renders Lunar templates with variable injection.
 */
class TemplateRenderer implements RendererInterface
{
    private PathValidator $pathValidator;

    private CompilerInterface $compiler;

    private string $cachePath;

    private FilterRegistry $filterRegistry;

    /** @var array<string, callable> */
    private array $macros = [];

    /** @var array<string, mixed> */
    private array $defaultVariables = [];

    /**
     * @param string $templatePath Path to templates directory
     * @param string $cachePath Path to cache directory
     * @param CompilerInterface|null $compiler Template compiler
     * @param FilterRegistry|null $filterRegistry Filter registry
     */
    public function __construct(
        string $templatePath,
        string $cachePath,
        ?CompilerInterface $compiler = null,
        ?FilterRegistry $filterRegistry = null,
    ) {
        $this->pathValidator = new PathValidator($templatePath);
        $this->cachePath = $this->normalizePath($cachePath);
        $this->compiler = $compiler ?? new TemplateCompiler();
        $this->filterRegistry = $filterRegistry ?? DefaultFilters::create();

        $this->ensureCacheDirectoryExists();
    }

    /**
     * {@inheritDoc}
     */
    public function render(string $template, array $variables = []): string
    {
        $templateFile = $this->resolveTemplatePath($template);

        if (!file_exists($templateFile)) {
            throw new TemplateNotFoundException($templateFile);
        }

        $compiledFile = $this->getCompiledPath($templateFile);

        if ($this->needsCompilation($compiledFile, $templateFile)) {
            $this->compileTemplate($templateFile, $compiledFile);
        }

        return $this->executeTemplate($compiledFile, $variables);
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $template): bool
    {
        try {
            $templateFile = $this->resolveTemplatePath($template);

            return file_exists($templateFile);
        } catch (TemplateException) {
            return false;
        }
    }

    /**
     * Register a macro.
     *
     * @param string $name Macro name
     * @param callable $callback Macro callback
     */
    public function registerMacro(string $name, callable $callback): void
    {
        $this->macros[$name] = $callback;
    }

    /**
     * Register a macro from an interface instance.
     */
    public function registerMacroInstance(MacroInterface $macro): void
    {
        $this->registerMacro($macro->getName(), [$macro, 'execute']);
    }

    /**
     * Set default variables.
     *
     * @param array<string, mixed> $variables Default variables
     */
    public function setDefaultVariables(array $variables): void
    {
        $this->defaultVariables = $variables;
    }

    /**
     * Add default variables.
     *
     * @param array<string, mixed> $variables Variables to add
     */
    public function addDefaultVariables(array $variables): void
    {
        $this->defaultVariables = array_merge($this->defaultVariables, $variables);
    }

    /**
     * Register a filter.
     *
     * @param string $name Filter name
     * @param callable $callback Filter callback
     */
    public function registerFilter(string $name, callable $callback): void
    {
        $this->filterRegistry->register($name, $callback);
    }

    /**
     * Register a filter from an interface instance.
     */
    public function registerFilterInstance(FilterInterface $filter): void
    {
        $this->filterRegistry->registerInstance($filter);
    }

    /**
     * Get the filter registry.
     */
    public function getFilterRegistry(): FilterRegistry
    {
        return $this->filterRegistry;
    }

    /**
     * Apply a filter to a value.
     *
     * @param string $name Filter name
     * @param mixed $value Value to filter
     * @param array<int, mixed> $args Filter arguments
     *
     * @return mixed Filtered value
     */
    public function applyFilter(string $name, mixed $value, array $args = []): mixed
    {
        return $this->filterRegistry->apply($name, $value, $args);
    }

    /**
     * Clear the template cache.
     *
     * @param string|null $template Specific template to clear, or all if null
     */
    public function clearCache(?string $template = null): void
    {
        if ($template !== null) {
            $templateFile = $this->resolveTemplatePath($template);
            $compiledFile = $this->getCompiledPath($templateFile);

            if (file_exists($compiledFile)) {
                unlink($compiledFile);
            }
        } else {
            $files = glob($this->cachePath . '/*.php');
            if ($files !== false) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Call a registered macro.
     *
     * @param string $name Macro name
     * @param array<int, mixed> $args Macro arguments
     *
     * @return mixed Macro result
     */
    public function callMacro(string $name, array $args): mixed
    {
        if (!isset($this->macros[$name])) {
            throw TemplateException::macroNotFound($name);
        }

        $callback = $this->macros[$name];

        if (\is_array($callback) && isset($callback[0]) && $callback[0] instanceof MacroInterface) {
            return $callback[0]->execute($args);
        }

        return $callback(...$args);
    }

    /**
     * Normalize a path.
     */
    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * Ensure cache directory exists.
     */
    private function ensureCacheDirectoryExists(): void
    {
        if (!is_dir($this->cachePath)) {
            if (!mkdir($this->cachePath, 0o755, true) && !is_dir($this->cachePath)) {
                // @codeCoverageIgnoreStart
                throw TemplateException::unableToCreateCacheDirectory($this->cachePath);
                // @codeCoverageIgnoreEnd
            }
        }
    }

    /**
     * Resolve template path.
     */
    private function resolveTemplatePath(string $template): string
    {
        $template = $this->ensureExtension($template);

        return $this->pathValidator->validate($template);
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

    /**
     * Get compiled file path.
     */
    private function getCompiledPath(string $templateFile): string
    {
        return $this->cachePath . '/' . md5($templateFile) . '.php';
    }

    /**
     * Check if template needs compilation.
     */
    private function needsCompilation(string $compiledFile, string $templateFile): bool
    {
        if (!file_exists($compiledFile)) {
            return true;
        }

        if (filemtime($compiledFile) < filemtime($templateFile)) {
            return true;
        }

        // Check dependencies (parents)
        $handle = fopen($compiledFile, 'r');
        if ($handle) {
            $line = fgets($handle);
            fclose($handle);

            if ($line !== false && str_starts_with($line, '<?php /* DEPENDENCIES: ')) {
                $depsString = substr($line, 23, strpos($line, ' */') - 23);
                $dependencies = explode(';', $depsString);

                foreach ($dependencies as $dep) {
                    if ($dep !== '' && file_exists($dep) && filemtime($compiledFile) < filemtime($dep)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Compile template to cache.
     */
    private function compileTemplate(string $templateFile, string $compiledFile): void
    {
        $source = file_get_contents($templateFile);
        if ($source === false) {
            // @codeCoverageIgnoreStart
            throw TemplateException::unableToReadTemplate($templateFile);
            // @codeCoverageIgnoreEnd
        }

        $dependencies = [];
        $source = $this->processExtends($source, $dependencies);
        $compiled = $this->compiler->compile($source);

        $header = '';
        if (!empty($dependencies)) {
            $header = '<?php /* DEPENDENCIES: ' . implode(';', $dependencies) . ' */ ?>' . PHP_EOL;
        }

        file_put_contents($compiledFile, $header . $compiled);
    }

    /**
     * Process template inheritance.
     *
     * @param array<string> $dependencies
     */
    private function processExtends(string $source, array &$dependencies = []): string
    {
        if (preg_match('/\[%\s*extends\s+[\'"](.+?)[\'"]\s*%\]/', $source, $matches)) {
            $parentTemplate = $matches[1];
            
            $source = (string) preg_replace('/\[%\s*extends\s+[\'"](.+?)[\'"]\s*%\]/', '', $source);
            $blocks = $this->extractBlocks($source);

            $parentFile = $this->resolveTemplatePath($parentTemplate);
            if (!file_exists($parentFile)) {
                throw TemplateException::parentTemplateNotFound($parentFile);
            }

            // Add to dependencies
            if (!in_array($parentFile, $dependencies, true)) {
                $dependencies[] = $parentFile;
            }

            $parentSource = file_get_contents($parentFile);
            if ($parentSource === false) {
                // @codeCoverageIgnoreStart
                throw TemplateException::unableToReadTemplate($parentFile);
                // @codeCoverageIgnoreEnd
            }

            // Recursively process parent extends
            $parentSource = $this->processExtends($parentSource, $dependencies);

            return (string) preg_replace_callback(
                '/\[%\s*block\s+(\w+)\s*%\](.*?)\[%\s*endblock\s*%\]/s',
                function ($matches) use ($blocks) {
                    $blockName = $matches[1];

                    return $blocks[$blockName] ?? $matches[2];
                },
                $parentSource,
            );
        }

        return $source;
    }

    /**
     * Extract blocks from template.
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
     * Execute compiled template.
     *
     * @param string $compiledFile Compiled file path
     * @param array<string, mixed> $variables Variables to inject
     *
     * @return string Rendered content
     */
    private function executeTemplate(string $compiledFile, array $variables): string
    {
        $variables = array_merge($this->defaultVariables, $variables);

        extract($variables, EXTR_OVERWRITE);

        ob_start();

        try {
            include $compiledFile;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return (string) ob_get_clean();
    }
}
