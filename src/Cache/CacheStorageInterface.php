<?php

declare(strict_types=1);

namespace Lunar\Template\Cache;

interface CacheStorageInterface
{
    /**
     * Retrieves an item from the cache by key.
     *
     * @param string $key The unique key of the item to retrieve.
     * @return string|null The value of the item, or null if the item doesn't exist.
     */
    public function get(string $key): ?string;

    /**
     * Stores an item in the cache.
     *
     * @param string $key The unique key of the item to store.
     * @param string $content The item to store.
     */
    public function set(string $key, string $content): void;

    /**
     * Deletes an item from the cache by key.
     *
     * @param string $key The unique cache key of the item to delete.
     */
    public function delete(string $key): void;

    /**
     * Deletes all items in the cache.
     */
    public function clear(): void;
}
