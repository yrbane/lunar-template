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
final class PluginDiscovery
{
    public function __construct(private readonly string $vendorDir)
    {
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
        $path = $this->vendorDir . '/composer/installed.json';
        if (!is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!\is_array($decoded)) {
            return [];
        }

        // Composer 2 stocke sous "packages", Composer 1 directement à la racine.
        $packages = $decoded['packages'] ?? $decoded;
        if (!\is_array($packages)) {
            return [];
        }

        return array_values(array_filter($packages, 'is_array'));
    }
}
