<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate Twitter Card meta tags.
 *
 * Usage:
 * - ##twitter("card", "summary_large_image")##
 * - ##twitter("title", "Page Title")##
 * - ##twitter("description", "Description")##
 * - ##twitter("image", "https://example.com/image.jpg")##
 */
final class TwitterCardMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'twitter';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $content = (string) ($args[1] ?? '');

        if ($name === '' || $content === '') {
            return '';
        }

        $twitterName = str_starts_with($name, 'twitter:') ? $name : 'twitter:' . $name;

        return '<meta name="' . htmlspecialchars($twitterName, ENT_QUOTES, 'UTF-8') . '" content="' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '">';
    }
}
