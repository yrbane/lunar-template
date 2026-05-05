<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Fixtures\Plugin;

use Lunar\Template\Macro\MacroInterface;

/**
 * Fixture pour les tests de PluginDiscovery (MOD-02).
 */
final class SamplePluginMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'sample_plugin';
    }

    public function execute(array $args)
    {
        return 'plugin-macro-output';
    }
}
