<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate Vimeo embed iframe.
 *
 * Usage:
 * - ##vimeo("123456789")## - Default embed
 * - ##vimeo("123456789", 640, 360)## - Custom size
 */
final class EmbedVimeoMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'vimeo';
    }

    public function execute(array $args): string
    {
        $videoId = $this->extractVideoId((string) ($args[0] ?? ''));
        $width = (int) ($args[1] ?? 640);
        $height = (int) ($args[2] ?? 360);

        if ($videoId === '') {
            return '';
        }

        $url = 'https://player.vimeo.com/video/' . $videoId . '?dnt=1';

        return \sprintf(
            '<iframe width="%d" height="%d" src="%s" title="Vimeo video" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>',
            $width,
            $height,
            htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
        );
    }

    private function extractVideoId(string $input): string
    {
        // Already an ID
        if (preg_match('/^\d+$/', $input)) {
            return $input;
        }

        // Full URL
        if (preg_match('/vimeo\.com\/(\d+)/', $input, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
