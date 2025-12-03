<?php

declare(strict_types=1);

namespace Lunar\Template\Cache;

/**
 * Interface for template cache backends.
 */
interface CacheInterface
{
    /**
     * Check if a cached item exists and is fresh.
     *
     * @param string $key Cache key
     * @param int $sourceModifiedTime Source modification timestamp
     *
     * @return bool True if cache is valid
     */
    public function has(string $key, int $sourceModifiedTime): bool;

    /**
     * Get a cached item.
     *
     * @param string $key Cache key
     *
     * @return string|null Cached content or null
     */
    public function get(string $key): ?string;

    /**
     * Store an item in cache.
     *
     * @param string $key Cache key
     * @param string $content Content to cache
     */
    public function set(string $key, string $content): void;

    /**
     * Remove an item from cache.
     *
     * @param string $key Cache key
     */
    public function delete(string $key): void;

    /**
     * Clear the entire cache.
     */
    public function clear(): void;

    /**
     * Get the path to a cached file (for include).
     *
     * @param string $key Cache key
     *
     * @return string|null File path or null if not cached
     */
    public function getPath(string $key): ?string;
}
