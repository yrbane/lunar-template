<?php

declare(strict_types=1);

namespace Lunar\Template\Cache;

use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Cache\CacheStorageInterface;

/**
 * Filesystem-based template cache storage.
 */
class FilesystemCache implements CacheStorageInterface
{
    private string $directory;

    private string $extension;

    public function __construct(string $directory, string $extension = '.php')
    {
        $this->directory = rtrim($directory, '/\\');
        $this->extension = $extension;

        $this->ensureDirectoryExists();
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?string
    {
        $path = $this->getFilePath($key);

        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, string $content): void
    {
        $path = $this->getFilePath($key);

        file_put_contents($path, $content);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): void
    {
        $path = $this->getFilePath($key);

        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * {@inheritDoc}
     */
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

    /**
     * Get the cache directory.
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Generate file path for a cache key.
     */
    private function getFilePath(string $key): string
    {
        return $this->directory . '/' . $key . $this->extension;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompiledFilePath(string $key): string
    {
        return $this->getFilePath($key);
    }

    /**
     * Ensure cache directory exists.
     */
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