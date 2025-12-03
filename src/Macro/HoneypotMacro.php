<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate honeypot field for spam protection.
 *
 * Usage:
 * - ##honeypot()## - Default honeypot
 * - ##honeypot("website")## - Custom field name
 */
final class HoneypotMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'honeypot';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? 'website_url');

        return '<div style="position:absolute;left:-9999px;"><input type="text" name="' .
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8') .
            '" value="" tabindex="-1" autocomplete="off"></div>';
    }
}
