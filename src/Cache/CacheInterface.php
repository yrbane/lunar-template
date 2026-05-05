<?php

declare(strict_types=1);

namespace Lunar\Template\Cache;

/**
 * Contrat d'un cache de templates compilés.
 *
 * Les implémentations doivent stocker un blob (le template PHP compilé)
 * indexé par une clé arbitraire et permettre d'invalider en fonction
 * de la date de modification de la source.
 */
interface CacheInterface
{
    /**
     * Récupère un élément du cache par sa clé.
     *
     * @return string|null Le contenu, ou null si absent.
     */
    public function get(string $key): ?string;

    /**
     * Stocke un élément dans le cache.
     */
    public function set(string $key, string $content): void;

    /**
     * Indique si l'élément est présent ET à jour vis-à-vis du timestamp source.
     *
     * @param int $sourceTime mtime du fichier source — l'entrée est considérée
     *                        périmée si elle est plus ancienne que ce timestamp.
     */
    public function has(string $key, int $sourceTime): bool;

    /**
     * Supprime un élément du cache.
     */
    public function delete(string $key): void;

    /**
     * Vide intégralement le cache.
     */
    public function clear(): void;

    /**
     * Retourne le chemin physique du fichier compilé pour cette clé,
     * ou null si l'élément n'existe pas.
     *
     * Utile pour permettre à l'engine d'`include` directement le fichier
     * (cache filesystem) plutôt que de passer par `get()` + `eval()`.
     */
    public function getPath(string $key): ?string;

    /**
     * Retourne le répertoire de base du cache.
     */
    public function getDirectory(): string;
}
