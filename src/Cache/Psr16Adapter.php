<?php

declare(strict_types=1);

namespace Lunar\Template\Cache;

use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

/**
 * Adapte un cache PSR-16 (Redis, Memcached, APCu, …) à l'interface
 * cache de Lunar.
 *
 * Le contrat Lunar exige `getPath(string)` pour permettre `include`
 * du fichier compilé. Les caches PSR-16 ne connaissent pas la notion
 * de chemin physique : l'adapter matérialise donc le contenu dans
 * un répertoire local à la demande, en se ré-alignant sur le
 * timestamp stocké côté PSR-16 quand celui-ci est plus récent.
 */
final readonly class Psr16Adapter implements CacheInterface
{
    private string $writeDir;

    public function __construct(
        private Psr16CacheInterface $psr,
        string $writeDir,
        private string $extension = '.php',
    ) {
        $this->writeDir = rtrim($writeDir, '/\\');

        if (!is_dir($this->writeDir)) {
            mkdir($this->writeDir, 0o755, true);
        }
    }

    public function get(string $key): ?string
    {
        $value = $this->psr->get($key);

        return \is_string($value) ? $value : null;
    }

    public function set(string $key, string $content): void
    {
        $this->psr->set($key, $content);
        $this->psr->set($this->mtimeKey($key), time());

        // Matérialise immédiatement pour permettre include().
        file_put_contents($this->localPath($key), $content);
    }

    public function has(string $key, int $sourceTime): bool
    {
        $mtime = $this->psr->get($this->mtimeKey($key));

        if (!\is_int($mtime)) {
            return false;
        }

        return $mtime >= $sourceTime;
    }

    public function delete(string $key): void
    {
        $this->psr->delete($key);
        $this->psr->delete($this->mtimeKey($key));

        $local = $this->localPath($key);
        if (file_exists($local)) {
            unlink($local);
        }
    }

    public function clear(): void
    {
        $this->psr->clear();

        $files = glob($this->writeDir . '/*' . $this->extension);
        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    public function getPath(string $key): ?string
    {
        $value = $this->psr->get($key);
        if (!\is_string($value)) {
            return null;
        }

        $path = $this->localPath($key);

        // Matérialise (ou re-matérialise) si nécessaire.
        if (!file_exists($path) || file_get_contents($path) !== $value) {
            file_put_contents($path, $value);
        }

        return $path;
    }

    public function getDirectory(): string
    {
        return $this->writeDir;
    }

    /**
     * Clé compagne stockant le timestamp d'écriture pour les vérifications
     * de fraîcheur (équivalent du mtime filesystem).
     */
    private function mtimeKey(string $key): string
    {
        return $key . '.lunar_mtime';
    }

    private function localPath(string $key): string
    {
        return $this->writeDir . '/' . $key . $this->extension;
    }
}
