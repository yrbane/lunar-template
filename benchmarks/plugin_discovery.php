<?php

declare(strict_types=1);

/**
 * Benchmark : Cache statique de PluginDiscovery.
 *
 * Usage : php benchmarks/plugin_discovery.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Lunar\Template\Plugin\PluginDiscovery;

const ITERATIONS = 1000;

$tmp = sys_get_temp_dir() . '/bench-plugin-' . uniqid();
mkdir($tmp . '/composer', 0o755, true);

// Faux installed.json plausible (~50 packages)
$packages = [];
for ($i = 0; $i < 50; $i++) {
    $packages[] = [
        'name' => "vendor/pkg-{$i}",
        'extra' => $i % 5 === 0 ? ['lunar-template' => ['macros' => []]] : [],
    ];
}
file_put_contents(
    "$tmp/composer/installed.json",
    json_encode(['packages' => $packages]) ?: '{}',
);

PluginDiscovery::clearCache();
$start = hrtime(true);
for ($i = 0; $i < ITERATIONS; $i++) {
    PluginDiscovery::clearCache(); // force re-scan à chaque itération
    (new PluginDiscovery($tmp))->discoverMacros();
}
$noCache = (hrtime(true) - $start) / 1e6;

PluginDiscovery::clearCache();
$start = hrtime(true);
for ($i = 0; $i < ITERATIONS; $i++) {
    (new PluginDiscovery($tmp))->discoverMacros();
}
$withCache = (hrtime(true) - $start) / 1e6;

printf("PluginDiscovery — %s itérations (50 packages dans installed.json)\n", number_format(ITERATIONS));
echo str_repeat('-', 70) . "\n";
printf("Sans cache (re-scan)    : %8.2f ms total | %6.3f ms/op\n", $noCache, $noCache / ITERATIONS);
printf("Avec cache (mémoïsé)   : %8.2f ms total | %6.3f ms/op\n", $withCache, $withCache / ITERATIONS);
printf("Speedup                : %.1fx\n", $noCache / max($withCache, 0.01));

// Cleanup
unlink("$tmp/composer/installed.json");
rmdir("$tmp/composer");
rmdir($tmp);
