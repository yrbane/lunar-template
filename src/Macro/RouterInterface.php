<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

interface RouterInterface
{
    /**
     * Get route configuration by name.
     *
     * @param string $name Route name
     *
     * @return array{path: string, params?: array<string, string>}|null Route configuration or null if not found
     */
    public function getRouteByName(string $name): ?array;
}
