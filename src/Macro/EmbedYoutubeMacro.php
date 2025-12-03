<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate YouTube embed iframe.
 *
 * Usage:
 * - ##youtube("dQw4w9WgXcQ")## - Default embed
 * - ##youtube("dQw4w9WgXcQ", 560, 315)## - Custom size
 * - ##youtube("dQw4w9WgXcQ", 560, 315, true)## - Autoplay
 */
final class EmbedYoutubeMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'youtube';
    }

    public function execute(array $args): string
    {
        $videoId = $this->extractVideoId((string) ($args[0] ?? ''));
        $width = (int) ($args[1] ?? 560);
        $height = (int) ($args[2] ?? 315);
        $autoplay = (bool) ($args[3] ?? false);

        if ($videoId === '') {
            return '';
        }

        $params = ['rel=0'];
        if ($autoplay) {
            $params[] = 'autoplay=1';
        }

        $url = 'https://www.youtube-nocookie.com/embed/' . $videoId . '?' . implode('&', $params);

        return sprintf(
            '<iframe width="%d" height="%d" src="%s" title="YouTube video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>',
            $width,
            $height,
            htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
        );
    }

    private function extractVideoId(string $input): string
    {
        // Already an ID
        if (preg_match('/^[\w-]{11}$/', $input)) {
            return $input;
        }

        // Full URL
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{11})/', $input, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
