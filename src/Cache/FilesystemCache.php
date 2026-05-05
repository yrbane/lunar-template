<?php

declare(strict_types=1);

namespace Lunar\Template\Cache;

use Lunar\Template\Exception\TemplateException;

/**
 * Cache de templates compilés basé sur le système de fichiers.
 */
class FilesystemCache implements CacheInterface
{
    private string $directory;

    private string $extension;

    public function __construct(string $directory, string $extension = '.php')
    {
        $this->directory = rtrim($directory, '/\\');
        $this->extension = $extension;

        $this->ensureDirectoryExists();
    }

    public function get(string $key): ?string
    {
        $path = $this->getFilePath($key);

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }

    public function set(string $key, string $content): void
    {
        file_put_contents($this->getFilePath($key), $content);
    }

    public function has(string $key, int $sourceTime): bool
    {
        $path = $this->getFilePath($key);

        if (!file_exists($path)) {
            return false;
        }

        return filemtime($path) >= $sourceTime;
    }

    public function delete(string $key): void
    {
        $path = $this->getFilePath($key);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function clear(): void
    {
        $pattern = $this->directory . '/*' . $this->extension;
        $files = glob($pattern);

        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    public function getPath(string $key): ?string
    {
        $path = $this->getFilePath($key);

        return file_exists($path) ? $path : null;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    private function getFilePath(string $key): string
    {
        return $this->directory . '/' . $key . $this->extension;
    }

    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->directory)) {
            if (!mkdir($this->directory, 0o755, true) && !is_dir($this->directory)) {
                // @codeCoverageIgnoreStart
                throw TemplateException::unableToCreateCacheDirectory($this->directory);
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
