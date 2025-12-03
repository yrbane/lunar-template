<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate initial-based avatar using UI Avatars service.
 *
 * Usage:
 * - ##avatar("John Doe")## - Default avatar
 * - ##avatar("John Doe", 100)## - 100px size
 * - ##avatar("John Doe", 80, "4F46E5", "ffffff")## - Custom colors
 */
final class AvatarMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'avatar';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '?');
        $size = (int) ($args[1] ?? 80);
        $background = (string) ($args[2] ?? '4F46E5');
        $color = (string) ($args[3] ?? 'ffffff');
        $rounded = (bool) ($args[4] ?? true);

        return \sprintf(
            'https://ui-avatars.com/api/?name=%s&size=%d&background=%s&color=%s&rounded=%s',
            urlencode($name),
            $size,
            $background,
            $color,
            $rounded ? 'true' : 'false',
        );
    }
}
