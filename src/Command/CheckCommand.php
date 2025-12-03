<?php

declare(strict_types=1);

namespace Lunar\Template\Command;

use Lunar\Cli\AbstractCommand;
use Lunar\Cli\Attribute\Command;
use Lunar\Cli\Helper\ConsoleHelper as C;
use Lunar\Template\AdvancedTemplateEngine;

#[Command(name: 'template:check', description: 'Check template syntax for errors')]
final class CheckCommand extends AbstractCommand
{
    public function execute(array $args): int
    {
        if ($this->wantsHelp($args)) {
            echo $this->getHelp();

            return 0;
        }

        $namedArgs = $this->parseNamedArgs($args);
        $templatePath = $this->getOptionValue($namedArgs, 'templates', getcwd() . '/templates');
        $cachePath = $this->getOptionValue($namedArgs, 'cache', sys_get_temp_dir() . '/lunar-template-check');
        $extension = $this->getOptionValue($namedArgs, 'ext', 'tpl');
        $template = $this->getFirstPositionalArgument($args);

        if (!is_dir($templatePath)) {
            C::error("Template directory not found: {$templatePath}");

            return 1;
        }

        try {
            $engine = new AdvancedTemplateEngine($templatePath, $cachePath);

            if ($template !== null) {
                return $this->checkSingleTemplate($engine, $template);
            }

            return $this->checkAllTemplates($engine, $templatePath, $extension);
        } catch (\Throwable $e) {
            C::error("Check failed: {$e->getMessage()}");

            return 1;
        }
    }

    private function checkSingleTemplate(AdvancedTemplateEngine $engine, string $template): int
    {
        C::subtitle("Checking template: {$template}");

        try {
            // Render with empty data to trigger compilation and check syntax
            $engine->render($template, []);
            C::success("Template is valid");

            return 0;
        } catch (\Throwable $e) {
            C::error("Syntax error: {$e->getMessage()}");

            return 1;
        }
    }

    private function checkAllTemplates(AdvancedTemplateEngine $engine, string $templatePath, string $extension): int
    {
        C::subtitle("Checking all templates");
        echo "Templates: {$templatePath}\n";
        echo "\n";

        $templates = $this->findTemplates($templatePath, $extension);

        if (empty($templates)) {
            C::warning("No templates found with extension .{$extension}");

            return 0;
        }

        $valid = 0;
        $invalid = 0;
        $errors = [];

        foreach ($templates as $templateFile) {
            $relativePath = str_replace($templatePath . '/', '', $templateFile);
            $templateName = preg_replace('/\.' . preg_quote($extension, '/') . '$/', '', $relativePath);

            try {
                // Render with empty data to trigger compilation and check syntax
                $engine->render($templateName, []);
                C::success("OK: {$templateName}");
                $valid++;
            } catch (\Throwable $e) {
                C::error("ERROR: {$templateName}");
                $errors[$templateName] = $e->getMessage();
                $invalid++;
            }
        }

        echo "\n";
        C::subtitle('Summary');
        echo "  Valid: {$valid}\n";
        echo "  Invalid: {$invalid}\n";

        if (!empty($errors)) {
            echo "\n";
            C::subtitle('Errors');
            foreach ($errors as $name => $message) {
                echo "  {$name}:\n";
                C::error("    {$message}");
            }
        }

        return $invalid > 0 ? 1 : 0;
    }

    /**
     * @return array<string>
     */
    private function findTemplates(string $directory, string $extension): array
    {
        $templates = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
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
Command: template:check
Check template syntax for errors.

Usage:
  lunar-template template:check [<template>] [options]

Arguments:
  template              Template name to check (optional, checks all if omitted)

Options:
  --templates=<path>    Templates directory (default: ./templates)
  --cache=<path>        Cache directory for compilation (default: system temp)
  --ext=<extension>     Template file extension (default: tpl)
  --help                Show this help

Examples:
  lunar-template template:check                   # Check all templates
  lunar-template template:check home              # Check single template
  lunar-template template:check --ext=twig        # Check all .twig templates
  lunar-template template:check --templates=/app/views

HELP;
    }
}
