<?php

declare(strict_types=1);

namespace Lunar\Template\Security;

use Lunar\Template\Exception\TemplateException;

/**
 * Validates template paths to prevent directory traversal attacks.
 */
class PathValidator
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $realPath = realpath($basePath);
        if ($realPath === false) {
            throw new TemplateException(\sprintf('Base path does not exist: %s', $basePath));
        }
        $this->basePath = $realPath;
    }

    /**
     * Validate that a path is within the allowed base directory.
     *
     * @throws TemplateException If path traversal is detected
     */
    public function validate(string $path): string
    {
        // Normalize the path
        $normalizedPath = $this->normalizePath($path);

        // Build full path
        $fullPath = $this->basePath . '/' . $normalizedPath;

        // Get real path (resolves symlinks and ..)
        $realPath = realpath($fullPath);

        // If file doesn't exist yet, check the directory
        if ($realPath === false) {
            $dirPath = \dirname($fullPath);
            $realDirPath = realpath($dirPath);

            if ($realDirPath === false || !str_starts_with($realDirPath, $this->basePath)) {
                throw new TemplateException(
                    \sprintf('Path traversal detected: %s', $path),
                );
            }

            return $fullPath;
        }

        // Verify the real path is within base path
        if (!str_starts_with($realPath, $this->basePath)) {
            throw new TemplateException(
                \sprintf('Path traversal detected: %s', $path),
            );
        }

        return $realPath;
    }

    /**
     * Normalize a path by removing dangerous sequences.
     */
    private function normalizePath(string $path): string
    {
        // Convert backslashes to forward slashes
        $path = str_replace('\\', '/', $path);

        // Remove null bytes
        $path = str_replace("\0", '', $path);

        // Remove leading slashes
        $path = ltrim($path, '/');

        return $path;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
