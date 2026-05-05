<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Plugin;

use Lunar\Template\Filter\FilterInterface;
use Lunar\Template\Macro\MacroInterface;
use Lunar\Template\Plugin\PluginDiscovery;
use PHPUnit\Framework\TestCase;

class PluginDiscoveryTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/lunar_plugins_' . uniqid();
        mkdir($this->tempDir . '/composer', 0o755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testDiscoversMacrosFromExtra(): void
    {
        $this->writeInstalledJson([
            [
                'name' => 'vendor/sample',
                'extra' => [
                    'lunar-template' => [
                        'macros' => [
                            'Lunar\\Template\\Tests\\Fixtures\\Plugin\\SamplePluginMacro',
                        ],
                    ],
                ],
            ],
        ]);

        $discovery = new PluginDiscovery($this->tempDir);
        $macros = $discovery->discoverMacros();

        $this->assertCount(1, $macros);
        $this->assertInstanceOf(MacroInterface::class, $macros[0]);
        $this->assertSame('sample_plugin', $macros[0]->getName());
    }

    public function testDiscoversFiltersFromExtra(): void
    {
        $this->writeInstalledJson([
            [
                'name' => 'vendor/sample',
                'extra' => [
                    'lunar-template' => [
                        'filters' => [
                            'Lunar\\Template\\Tests\\Fixtures\\Plugin\\SamplePluginFilter',
                        ],
                    ],
                ],
            ],
        ]);

        $discovery = new PluginDiscovery($this->tempDir);
        $filters = $discovery->discoverFilters();

        $this->assertCount(1, $filters);
        $this->assertInstanceOf(FilterInterface::class, $filters[0]);
        $this->assertSame('sample_plugin_filter', $filters[0]->getName());
    }

    public function testIgnoresPackagesWithoutLunarTemplateExtra(): void
    {
        $this->writeInstalledJson([
            ['name' => 'unrelated/package', 'extra' => []],
            ['name' => 'unrelated/package2'], // pas de extra
        ]);

        $discovery = new PluginDiscovery($this->tempDir);

        $this->assertSame([], $discovery->discoverMacros());
        $this->assertSame([], $discovery->discoverFilters());
    }

    public function testIgnoresMissingClass(): void
    {
        $this->writeInstalledJson([
            [
                'name' => 'vendor/broken',
                'extra' => [
                    'lunar-template' => [
                        'macros' => ['Vendor\\NonExistent\\Class'],
                    ],
                ],
            ],
        ]);

        $discovery = new PluginDiscovery($this->tempDir);

        $this->assertSame([], $discovery->discoverMacros());
    }

    public function testIgnoresClassNotImplementingInterface(): void
    {
        $this->writeInstalledJson([
            [
                'name' => 'vendor/wrong-iface',
                'extra' => [
                    'lunar-template' => [
                        'macros' => ['stdClass'], // n'implémente pas MacroInterface
                    ],
                ],
            ],
        ]);

        $discovery = new PluginDiscovery($this->tempDir);

        $this->assertSame([], $discovery->discoverMacros());
    }

    public function testReturnsEmptyForMissingInstalledJson(): void
    {
        // Pas de installed.json créé
        $discovery = new PluginDiscovery($this->tempDir);

        $this->assertSame([], $discovery->discoverMacros());
        $this->assertSame([], $discovery->discoverFilters());
    }

    public function testReturnsEmptyForInvalidInstalledJson(): void
    {
        file_put_contents($this->tempDir . '/composer/installed.json', '{not json}');

        $discovery = new PluginDiscovery($this->tempDir);

        $this->assertSame([], $discovery->discoverMacros());
    }

    /**
     * @param array<int, array<string, mixed>> $packages
     */
    private function writeInstalledJson(array $packages): void
    {
        $json = ['packages' => $packages];
        file_put_contents(
            $this->tempDir . '/composer/installed.json',
            json_encode($json, JSON_PRETTY_PRINT) ?: '{}',
        );
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $f) {
            $p = $dir . '/' . $f;
            is_dir($p) ? $this->removeDirectory($p) : unlink($p);
        }
        rmdir($dir);
    }
}
