<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Fixtures\Plugin;

use Lunar\Template\Filter\FilterInterface;

/**
 * Fixture pour les tests de PluginDiscovery (MOD-02).
 */
final class SamplePluginFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'sample_plugin_filter';
    }

    public function apply(mixed $value, array $args = []): string
    {
        return '[' . (string) $value . ']';
    }
}
