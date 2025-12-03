<?php

declare(strict_types=1);

namespace Lunar\Template\Command;

use Lunar\Cli\AbstractCommand;
use Lunar\Cli\Attribute\Command;
use Lunar\Cli\Helper\ConsoleHelper as C;
use Lunar\Template\AdvancedTemplateEngine;

#[Command(name: 'template:compile', description: 'Compile a template without rendering')]
final class CompileCommand extends AbstractCommand
{
    public function execute(array $args): int
    {
        if ($this->wantsHelp($args)) {
            echo $this->getHelp();

            return 0;
        }

        $namedArgs = $this->parseNamedArgs($args);
        $templatePath = $this->getOptionValue($namedArgs, 'templates', getcwd() . '/templates');
        $cachePath = $this->getOptionValue($namedArgs, 'cache', getcwd() . '/cache');
        $template = $this->getFirstPositionalArgument($args);

        if ($template === null) {
            C::error('Template name is required');

            return 1;
        }

        if (!is_dir($templatePath)) {
            C::error("Template directory not found: {$templatePath}");

            return 1;
        }

        try {
            C::subtitle("Compiling template: {$template}");

            $engine = new AdvancedTemplateEngine($templatePath, $cachePath);
            // Render with empty data to trigger compilation
            $engine->render($template, []);

            C::success("Template compiled successfully");

            return 0;
        } catch (\Throwable $e) {
            C::error("Compilation failed: {$e->getMessage()}");

            return 1;
        }
    }

    public function getHelp(): string
    {
        return <<<'HELP'
Command: template:compile
Compile a template without rendering it.

Usage:
  lunar-template template:compile <template> [options]

Arguments:
  template              Template name (without extension)

Options:
  --templates=<path>    Templates directory (default: ./templates)
  --cache=<path>        Cache directory (default: ./cache)
  --help                Show this help

Examples:
  lunar-template template:compile home
  lunar-template template:compile blog/article
  lunar-template template:compile page --templates=/app/views --cache=/tmp/cache

HELP;
    }
}
