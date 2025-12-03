<?php

declare(strict_types=1);

namespace Lunar\Template\Command;

use FilesystemIterator;
use Lunar\Cli\AbstractCommand;
use Lunar\Cli\Attribute\Command;
use Lunar\Cli\Helper\ConsoleHelper as C;
use Lunar\Template\Config;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

#[Command(name: 'template:clear', description: 'Clear the template cache')]
final class ClearCommand extends AbstractCommand
{
    public function execute(array $args): int
    {
        if ($this->wantsHelp($args)) {
            echo $this->getHelp();

            return 0;
        }

        $namedArgs = $this->parseNamedArgs($args);
        $cachePath = $this->getOptionValue($namedArgs, 'cache', Config::getCachePath());
        $force = $this->hasFlag($namedArgs, 'force');

        if (!is_dir($cachePath)) {
            C::warning("Cache directory does not exist: {$cachePath}");

            return 0;
        }

        try {
            C::subtitle('Clearing template cache');
            echo "Cache: {$cachePath}\n";
            echo "\n";

            $files = $this->findCacheFiles($cachePath);

            if (empty($files)) {
                C::success('Cache is already empty');

                return 0;
            }

            if (!$force) {
                C::warning('Found ' . \count($files) . ' cached file(s)');
                echo "Use --force to confirm deletion\n";

                return 0;
            }

            $deleted = 0;
            $failed = 0;

            foreach ($files as $file) {
                if (@unlink($file)) {
                    $deleted++;
                } else {
                    $failed++;
                    C::error("  Failed to delete: {$file}");
                }
            }

            echo "\n";
            C::success("Deleted {$deleted} cached file(s)");

            if ($failed > 0) {
                C::warning("Failed to delete {$failed} file(s)");

                return 1;
            }

            return 0;
        } catch (Throwable $e) {
            C::error("Cache clearing failed: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * @return array<string>
     */
    private function findCacheFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile()) {
                if ($file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    public function getHelp(): string
    {
        return <<<'HELP'
            Command: template:clear
            Clear the template cache.

            Usage:
              lunar-template template:clear [options]

            Options:
              --cache=<path>        Cache directory (default: ./cache)
              --force               Actually delete the files (required)
              --help                Show this help

            Examples:
              lunar-template template:clear                   # Shows what would be deleted
              lunar-template template:clear --force           # Actually deletes cache
              lunar-template template:clear --cache=/tmp/cache --force

            HELP;
    }
}
