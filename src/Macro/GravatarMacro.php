<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate Gravatar URL from email.
 *
 * Usage:
 * - ##gravatar("email@example.com")## - Default 80px
 * - ##gravatar("email@example.com", 200)## - 200px size
 * - ##gravatar("email@example.com", 100, "mp")## - With default avatar
 */
final class GravatarMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'gravatar';
    }

    public function execute(array $args): string
    {
        $email = (string) ($args[0] ?? '');
        $size = (int) ($args[1] ?? 80);
        $default = (string) ($args[2] ?? 'mp');

        $hash = md5(strtolower(trim($email)));

        return sprintf(
            'https://www.gravatar.com/avatar/%s?s=%d&d=%s',
            $hash,
            $size,
            urlencode($default)
        );
    }
}
