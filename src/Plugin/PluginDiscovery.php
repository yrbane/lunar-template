<?php

declare(strict_types=1);

namespace Lunar\Template\Plugin;

use Lunar\Template\Filter\FilterInterface;
use Lunar\Template\Macro\MacroInterface;

/**
 * Découvre les macros et filtres déclarés par les packages tiers
 * via la section `extra.lunar-template` de leur `composer.json`.
 *
 * Format attendu côté package consommateur :
 *
 *   "extra": {
 *     "lunar-template": {
 *       "macros": ["Vendor\\Package\\MyMacro"],
 *       "filters": ["Vendor\\Package\\MyFilter"]
 *     }
 *   }
 *
 * La discovery lit `vendor/composer/installed.json` (généré par Composer
 * à `composer install`) plutôt que de scanner chaque composer.json
 * individuellement, ce qui est suffisant pour la majorité des cas et
 * évite la traversée du filesystem.
 */
/**
 * @note Pas marquée `readonly class` à cause d'un cache statique :
 *       PHP 8.5 interdit les valeurs par défaut sur les propriétés
 *       statiques d'une readonly class. La prop d'instance reste
 *       `private readonly` pour conserver l'esprit de MOD-03.
 */
final class PluginDiscovery
{
    /**
     * Cache statique du contenu de installed.json indexé par vendorDir.
     *
     * Permet d'éviter une relecture/decode JSON à chaque appel
     * discoverMacros() / discoverFilters() (et à chaque instanciation
     * d'un nouvel engine dans le même process). La clé est le chemin
     * absolu, donc plusieurs vendorDir distincts coexistent sans collision.
     *
     * @var array<string, list<array<string, mixed>>>
     */
    private static array $packagesCache = [];

    public function __construct(private readonly string $vendorDir)
    {
    }

    /**
     * Réinitialise le cache statique (pour les tests ou un long-running
     * process qui voudrait re-scanner après une mise à jour Composer).
     */
    public static function clearCache(): void
    {
        self::$packagesCache = [];
    }

    /**
     * @return list<MacroInterface>
     */
    public function discoverMacros(): array
    {
        return $this->discover('macros', MacroInterface::class);
    }

    /**
     * @return list<FilterInterface>
     */
    public function discoverFilters(): array
    {
        return $this->discover('filters', FilterInterface::class);
    }

    /**
     * @template T of object
     * @param class-string<T> $expectedInterface
     * @return list<T>
     */
    private function discover(string $key, string $expectedInterface): array
    {
        $found = [];

        foreach ($this->readPackages() as $package) {
            $classes = $package['extra']['lunar-template'][$key] ?? null;
            if (!\is_array($classes)) {
                continue;
            }

            foreach ($classes as $class) {
                if (!\is_string($class) || !class_exists($class)) {
                    continue;
                }

                if (!is_subclass_of($class, $expectedInterface)) {
                    continue;
                }

                $instance = new $class();
                if ($instance instanceof $expectedInterface) {
                    $found[] = $instance;
                }
            }
        }

        return $found;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readPackages(): array
    {
        if (\array_key_exists($this->vendorDir, self::$packagesCache)) {
            return self::$packagesCache[$this->vendorDir];
        }

        $path = $this->vendorDir . '/composer/installed.json';
        if (!is_file($path)) {
            return self::$packagesCache[$this->vendorDir] = [];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return self::$packagesCache[$this->vendorDir] = [];
        }

        $decoded = json_decode($raw, true);
        if (!\is_array($decoded)) {
            return self::$packagesCache[$this->vendorDir] = [];
        }

        // Composer 2 stocke sous "packages", Composer 1 directement à la racine.
        $packages = $decoded['packages'] ?? $decoded;
        if (!\is_array($packages)) {
            return self::$packagesCache[$this->vendorDir] = [];
        }

        return self::$packagesCache[$this->vendorDir] = array_values(array_filter($packages, 'is_array'));
    }
}
