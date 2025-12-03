<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate CSRF token and hidden field.
 *
 * Usage:
 * - ##csrf()## - Returns hidden input with token
 * - ##csrf("token")## - Returns just the token
 * - ##csrf("meta")## - Returns meta tag
 */
final class CsrfMacro implements MacroInterface
{
    private ?string $token = null;

    public function getName(): string
    {
        return 'csrf';
    }

    public function execute(array $args): string
    {
        $mode = (string) ($args[0] ?? 'field');
        $token = $this->getToken();

        return match ($mode) {
            'token' => $token,
            'meta' => '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">',
            default => '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">',
        };
    }

    private function getToken(): string
    {
        if ($this->token === null) {
            $this->token = bin2hex(random_bytes(32));
        }

        return $this->token;
    }
}
