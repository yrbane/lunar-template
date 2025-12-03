<?php

declare(strict_types=1);

namespace Lunar\Template\Command;

use FilesystemIterator;
use Lunar\Cli\AbstractCommand;
use Lunar\Cli\Attribute\Command;
use Lunar\Cli\Helper\ConsoleHelper as C;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

#[Command(name: 'template:list', description: 'List all available templates')]
final class ListCommand extends AbstractCommand
{
    public function execute(array $args): int
    {
        if ($this->wantsHelp($args)) {
            echo $this->getHelp();

            return 0;
        }

        $namedArgs = $this->parseNamedArgs($args);
        $templatePath = $this->getOptionValue($namedArgs, 'templates', getcwd() . '/templates');
        $extension = $this->getOptionValue($namedArgs, 'ext', 'tpl');
        $showTree = $this->hasFlag($namedArgs, 'tree');

        if (!is_dir($templatePath)) {
            C::error("Template directory not found: {$templatePath}");

            return 1;
        }

        try {
            C::subtitle("Templates in: {$templatePath}");
            echo "\n";

            $templates = $this->findTemplates($templatePath, $extension);

            if (empty($templates)) {
                C::warning("No templates found with extension .{$extension}");

                return 0;
            }

            if ($showTree) {
                $this->displayTree($templates, $templatePath, $extension);
            } else {
                $this->displayList($templates, $templatePath, $extension);
            }

            echo "\n";
            echo 'Total: ' . \count($templates) . " template(s)\n";

            return 0;
        } catch (Throwable $e) {
            C::error("List failed: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * @param array<string> $templates
     */
    private function displayList(array $templates, string $templatePath, string $extension): void
    {
        foreach ($templates as $template) {
            $relativePath = str_replace($templatePath . '/', '', $template);
            $templateName = preg_replace('/\.' . preg_quote($extension, '/') . '$/', '', $relativePath);
            echo "  {$templateName}\n";
        }
    }

    /**
     * @param array<string> $templates
     */
    private function displayTree(array $templates, string $templatePath, string $extension): void
    {
        $tree = [];

        foreach ($templates as $template) {
            $relativePath = str_replace($templatePath . '/', '', $template);
            $templateName = preg_replace('/\.' . preg_quote($extension, '/') . '$/', '', $relativePath);
            $parts = explode('/', $templateName);
            $this->addToTree($tree, $parts);
        }

        $this->printTree($tree, '');
    }

    /**
     * @param array<string, mixed> $tree
     * @param array<string> $parts
     */
    private function addToTree(array &$tree, array $parts): void
    {
        $current = array_shift($parts);
        if ($current === null) {
            return;
        }

        if (!isset($tree[$current])) {
            $tree[$current] = [];
        }

        if (!empty($parts)) {
            $this->addToTree($tree[$current], $parts);
        }
    }

    /**
     * @param array<string, mixed> $tree
     */
    private function printTree(array $tree, string $prefix): void
    {
        $keys = array_keys($tree);
        $count = \count($keys);

        foreach ($keys as $i => $key) {
            $isLast = ($i === $count - 1);
            $connector = $isLast ? '└── ' : '├── ';
            $childPrefix = $isLast ? '    ' : '│   ';

            if (\is_array($tree[$key]) && !empty($tree[$key])) {
                echo $prefix . $connector . $key . "/\n";
                $this->printTree($tree[$key], $prefix . $childPrefix);
            } else {
                echo $prefix . $connector . $key . "\n";
            }
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
            Command: template:list
            List all available templates.

            Usage:
              lunar-template template:list [options]

            Options:
              --templates=<path>    Templates directory (default: ./templates)
              --ext=<extension>     Template file extension (default: tpl)
              --tree                Display templates as a tree structure
              --help                Show this help

            Examples:
              lunar-template template:list
              lunar-template template:list --tree
              lunar-template template:list --ext=twig
              lunar-template template:list --templates=/app/views --tree

            HELP;
    }
}
