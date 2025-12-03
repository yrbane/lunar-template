<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate QR code image URL.
 *
 * Usage:
 * - ##qrcode("https://example.com")## - Default 200x200
 * - ##qrcode("https://example.com", 300)## - 300x300 size
 * - ##qrcode("Hello World", 150, "img")## - Returns <img> tag
 */
final class QrCodeMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'qrcode';
    }

    public function execute(array $args): string
    {
        $data = (string) ($args[0] ?? '');
        $size = (int) ($args[1] ?? 200);
        $mode = (string) ($args[2] ?? 'url');

        if ($data === '') {
            return '';
        }

        $url = \sprintf(
            'https://api.qrserver.com/v1/create-qr-code/?size=%dx%d&data=%s',
            $size,
            $size,
            urlencode($data),
        );

        if ($mode === 'img') {
            return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="QR Code" width="' . $size . '" height="' . $size . '">';
        }

        return $url;
    }
}
