<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate security nonce for CSP.
 *
 * Usage:
 * - ##nonce()## - Generate unique nonce
 * - ##nonce("script")## - Script nonce attribute
 * - ##nonce("style")## - Style nonce attribute
 */
final class NonceMacro implements MacroInterface
{
    private ?string $nonce = null;

    public function getName(): string
    {
        return 'nonce';
    }

    public function execute(array $args): string
    {
        $mode = (string) ($args[0] ?? '');
        $nonce = $this->getNonce();

        return match ($mode) {
            'script' => 'nonce="' . $nonce . '"',
            'style' => 'nonce="' . $nonce . '"',
            default => $nonce,
        };
    }

    private function getNonce(): string
    {
        if ($this->nonce === null) {
            $this->nonce = base64_encode(random_bytes(16));
        }

        return $this->nonce;
    }
}
