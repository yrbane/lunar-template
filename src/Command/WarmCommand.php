<?php

declare(strict_types=1);

namespace Lunar\Template\Command;

use FilesystemIterator;
use Lunar\Cli\AbstractCommand;
use Lunar\Cli\Attribute\Command;
use Lunar\Cli\Helper\ConsoleHelper as C;
use Lunar\Template\AdvancedTemplateEngine;
use Lunar\Template\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

#[Command(name: 'template:warm', description: 'Pre-compile all templates to warm the cache')]
final class WarmCommand extends AbstractCommand
{
    public function execute(array $args): int
    {
        if ($this->wantsHelp($args)) {
            echo $this->getHelp();

            return 0;
        }

        $namedArgs = $this->parseNamedArgs($args);
        $templatePath = $this->getOptionValue($namedArgs, 'templates', Config::getTemplatePath());
        $cachePath = $this->getOptionValue($namedArgs, 'cache', Config::getCachePath());
        $extension = $this->getOptionValue($namedArgs, 'ext', Config::getExtension());

        if (!is_dir($templatePath)) {
            C::error("Template directory not found: {$templatePath}");

            return 1;
        }

        try {
            C::subtitle('Warming template cache');
            echo "Templates: {$templatePath}\n";
            echo "Cache: {$cachePath}\n";
            echo "\n";

            $engine = new AdvancedTemplateEngine($templatePath, $cachePath);
            $templates = $this->findTemplates($templatePath, $extension);

            if (empty($templates)) {
                C::warning("No templates found with extension .{$extension}");

                return 0;
            }

            $compiled = 0;
            $failed = 0;

            foreach ($templates as $template) {
                $relativePath = str_replace($templatePath . '/', '', $template);
                $templateName = preg_replace('/\.' . preg_quote($extension, '/') . '$/', '', $relativePath);

                try {
                    // Render with empty data to trigger compilation
                    $engine->render($templateName, []);
                    C::success("Compiled: {$templateName}");
                    $compiled++;
                } catch (Throwable $e) {
                    C::error("Failed: {$templateName} - {$e->getMessage()}");
                    $failed++;
                }
            }

            echo "\n";
            C::subtitle('Summary');
            echo "  Compiled: {$compiled}\n";
            if ($failed > 0) {
                echo "  Failed: {$failed}\n";
            }

            return $failed > 0 ? 1 : 0;
        } catch (Throwable $e) {
            C::error("Cache warming failed: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * @return array<string>
     */
    private function findTemplates(string $directory, string $extension): array
    {
        $templates = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile()) {
                if ($file->getExtension() === $extension) {
                    $templates[] = $file->getPathname();
                }
            }
        }

        sort($templates);

        return $templates;
    }

    public function getHelp(): string
    {
        return <<<'HELP'
            Command: template:warm
            Pre-compile all templates to warm the cache.

            Usage:
              lunar-template template:warm [options]

            Options:
              --templates=<path>    Templates directory (default: ./templates)
              --cache=<path>        Cache directory (default: ./cache)
              --ext=<extension>     Template file extension (default: tpl)
              --help                Show this help

            Examples:
              lunar-template template:warm
              lunar-template template:warm --templates=/app/views
              lunar-template template:warm --ext=twig
              lunar-template template:warm --templates=/app/views --cache=/tmp/cache

            HELP;
    }
}
