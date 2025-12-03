<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate social share URLs.
 *
 * Usage:
 * - ##share("twitter", "https://example.com", "Check this!")##
 * - ##share("facebook", "https://example.com")##
 * - ##share("linkedin", "https://example.com", "Title")##
 * - ##share("email", "https://example.com", "Subject", "Body")##
 * - ##share("whatsapp", "https://example.com", "Message")##
 */
final class ShareMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'share';
    }

    public function execute(array $args): string
    {
        $platform = strtolower((string) ($args[0] ?? ''));
        $url = (string) ($args[1] ?? '');
        $text = (string) ($args[2] ?? '');
        $extra = (string) ($args[3] ?? '');

        return match ($platform) {
            'twitter', 'x' => $this->twitter($url, $text),
            'facebook', 'fb' => $this->facebook($url),
            'linkedin' => $this->linkedin($url, $text),
            'email', 'mail' => $this->email($url, $text, $extra),
            'whatsapp', 'wa' => $this->whatsapp($url, $text),
            'telegram', 'tg' => $this->telegram($url, $text),
            'reddit' => $this->reddit($url, $text),
            'pinterest' => $this->pinterest($url, $text, $extra),
            'copy' => $url,
            default => '',
        };
    }

    private function twitter(string $url, string $text): string
    {
        $params = ['url' => $url];
        if ($text !== '') {
            $params['text'] = $text;
        }

        return 'https://twitter.com/intent/tweet?' . http_build_query($params);
    }

    private function facebook(string $url): string
    {
        return 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url);
    }

    private function linkedin(string $url, string $title): string
    {
        $params = ['url' => $url];
        if ($title !== '') {
            $params['title'] = $title;
        }

        return 'https://www.linkedin.com/sharing/share-offsite/?' . http_build_query($params);
    }

    private function email(string $url, string $subject, string $body): string
    {
        $fullBody = $body !== '' ? $body . "\n\n" . $url : $url;

        return 'mailto:?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($fullBody);
    }

    private function whatsapp(string $url, string $text): string
    {
        $message = $text !== '' ? $text . ' ' . $url : $url;

        return 'https://wa.me/?text=' . urlencode($message);
    }

    private function telegram(string $url, string $text): string
    {
        $params = ['url' => $url];
        if ($text !== '') {
            $params['text'] = $text;
        }

        return 'https://t.me/share/url?' . http_build_query($params);
    }

    private function reddit(string $url, string $title): string
    {
        $params = ['url' => $url];
        if ($title !== '') {
            $params['title'] = $title;
        }

        return 'https://reddit.com/submit?' . http_build_query($params);
    }

    private function pinterest(string $url, string $description, string $media): string
    {
        $params = ['url' => $url];
        if ($description !== '') {
            $params['description'] = $description;
        }
        if ($media !== '') {
            $params['media'] = $media;
        }

        return 'https://pinterest.com/pin/create/button/?' . http_build_query($params);
    }
}
