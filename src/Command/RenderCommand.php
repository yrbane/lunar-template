<?php

declare(strict_types=1);

namespace Lunar\Template\Command;

use Lunar\Cli\AbstractCommand;
use Lunar\Cli\Attribute\Command;
use Lunar\Cli\Helper\ConsoleHelper as C;
use Lunar\Template\AdvancedTemplateEngine;

#[Command(name: 'template:render', description: 'Render a template with variables')]
final class RenderCommand extends AbstractCommand
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
        $dataFile = $this->getOptionValue($namedArgs, 'data');
        $output = $this->getOptionValue($namedArgs, 'output');

        if ($template === null) {
            C::error('Template name is required');

            return 1;
        }

        if (!is_dir($templatePath)) {
            C::error("Template directory not found: {$templatePath}");

            return 1;
        }

        $data = [];
        if ($dataFile !== null) {
            if (!file_exists($dataFile)) {
                C::error("Data file not found: {$dataFile}");

                return 1;
            }
            $json = file_get_contents($dataFile);
            if ($json === false) {
                C::error("Cannot read data file: {$dataFile}");

                return 1;
            }
            $data = json_decode($json, true);
            if (!is_array($data)) {
                C::error("Invalid JSON in data file: {$dataFile}");

                return 1;
            }
        }

        try {
            C::subtitle("Rendering template: {$template}");

            $engine = new AdvancedTemplateEngine($templatePath, $cachePath);
            $html = $engine->render($template, $data);

            if ($output !== null) {
                $dir = dirname($output);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($output, $html);
                C::success("Output written to: {$output}");
            } else {
                echo $html;
            }

            return 0;
        } catch (\Throwable $e) {
            C::error("Render failed: {$e->getMessage()}");

            return 1;
        }
    }

    public function getHelp(): string
    {
        return <<<'HELP'
Command: template:render
Render a template with variables.

Usage:
  lunar-template template:render <template> [options]

Arguments:
  template              Template name (without extension)

Options:
  --templates=<path>    Templates directory (default: ./templates)
  --cache=<path>        Cache directory (default: ./cache)
  --data=<file>         JSON file with variables
  --output=<file>       Output file (default: stdout)
  --help                Show this help

Examples:
  lunar-template template:render home
  lunar-template template:render blog/article --data=article.json
  lunar-template template:render page --output=dist/page.html
  lunar-template template:render email --templates=/app/views --cache=/tmp/cache

HELP;
    }
}
