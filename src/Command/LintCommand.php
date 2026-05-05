<?php

declare(strict_types=1);

namespace Lunar\Template\Command;

use FilesystemIterator;
use Lunar\Cli\AbstractCommand;
use Lunar\Cli\Attribute\Command;
use Lunar\Cli\Helper\ConsoleHelper as C;
use Lunar\Template\Config;
use Lunar\Template\Linter\LintIssue;
use Lunar\Template\Linter\Linter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

#[Command(name: 'template:lint', description: 'Analyse statique des templates (sans exécution)')]
final class LintCommand extends AbstractCommand
{
    public function execute(array $args): int
    {
        if ($this->wantsHelp($args)) {
            echo $this->getHelp();

            return 0;
        }

        $namedArgs = $this->parseNamedArgs($args);
        $templatePath = $this->getOptionValue($namedArgs, 'templates', Config::getTemplatePath());
        $extension = $this->getOptionValue($namedArgs, 'ext', Config::getExtension());
        $template = $this->getFirstPositionalArgument($args);

        if (!is_dir($templatePath)) {
            C::error("Template directory not found: {$templatePath}");

            return 1;
        }

        try {
            $linter = new Linter();

            if ($template !== null) {
                return $this->lintSingleTemplate($linter, $templatePath, $template, $extension);
            }

            return $this->lintAllTemplates($linter, $templatePath, $extension);
        } catch (Throwable $e) {
            C::error("Lint failed: {$e->getMessage()}");

            return 1;
        }
    }

    private function lintSingleTemplate(Linter $linter, string $templatePath, string $template, string $extension): int
    {
        $file = $templatePath . '/' . $template;
        if (!str_ends_with($file, '.' . $extension)) {
            $file .= '.' . $extension;
        }

        if (!is_file($file)) {
            C::error("Template not found: {$file}");

            return 1;
        }

        $issues = $linter->lint((string) file_get_contents($file), $file);

        return $this->reportIssues($file, $issues);
    }

    private function lintAllTemplates(Linter $linter, string $templatePath, string $extension): int
    {
        C::subtitle('Lint des templates');
        echo "Templates: {$templatePath}\n\n";

        $templates = $this->findTemplates($templatePath, $extension);
        if (empty($templates)) {
            C::warning("Aucun template trouvé avec l'extension .{$extension}");

            return 0;
        }

        $totalIssues = 0;
        $invalidFiles = 0;

        foreach ($templates as $file) {
            $issues = $linter->lint((string) file_get_contents($file), $file);
            $exit = $this->reportIssues($file, $issues);
            if ($exit !== 0) {
                $invalidFiles++;
                $totalIssues += \count($issues);
            }
        }

        echo "\n";
        C::subtitle('Résumé');
        echo '  Fichiers analysés : ' . \count($templates) . "\n";
        echo "  Fichiers avec issues : {$invalidFiles}\n";
        echo "  Issues totales : {$totalIssues}\n";

        return $invalidFiles > 0 ? 1 : 0;
    }

    /**
     * @param list<LintIssue> $issues
     */
    private function reportIssues(string $file, array $issues): int
    {
        if ($issues === []) {
            C::success("OK: {$file}");

            return 0;
        }

        C::error("KO: {$file}");
        foreach ($issues as $issue) {
            $severity = $issue->severity === 'error' ? 'ERROR' : strtoupper($issue->severity);
            echo "    [{$severity}] {$file}:{$issue->line} — {$issue->message} ({$issue->type})\n";
        }

        return 1;
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
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === $extension) {
                $templates[] = $file->getPathname();
            }
        }

        sort($templates);

        return $templates;
    }

    public function getHelp(): string
    {
        return <<<'HELP'
            Command: template:lint
            Analyse statique des templates (sans les exécuter).

            Détecte :
              - blocs non fermés ([% if %] sans [% endif %], etc.)
              - fermetures orphelines ([% endif %] sans [% if %])
              - tokens non fermés ([[ var sans ]], [# .. sans #])
              - paires mal appariées

            Usage:
              lunar-template template:lint [<template>] [options]

            Arguments:
              template              Nom du template à linter (optionnel, lint tous les templates si omis)

            Options:
              --templates=<path>    Dossier des templates (défaut : ./templates)
              --ext=<extension>     Extension des fichiers (défaut : tpl)
              --help                Affiche cette aide

            Exemples:
              lunar-template template:lint                    # Lint tous les templates
              lunar-template template:lint home               # Lint un template précis
              lunar-template template:lint --ext=twig         # Lint les .twig

            HELP;
    }
}
