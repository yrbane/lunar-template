<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create an <iframe> element from a URL.
 *
 * Usage: [[ url | iframe ]]                      -> <iframe src="url"></iframe>
 * Usage: [[ url | iframe("Video") ]]             -> <iframe src="url" title="Video"></iframe>
 * Usage: [[ url | iframe("Video", "embed") ]]    -> <iframe src="url" title="Video" class="embed"></iframe>
 * Usage: [[ url | iframe("Video", "", true) ]]   -> <iframe src="url" title="Video" loading="lazy"></iframe>
 */
final class IframeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'iframe';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $src = $this->toString($value);

        if ($src === '') {
            return '';
        }

        $title = $args[0] ?? null;
        $class = $args[1] ?? null;
        $lazy = (bool) ($args[2] ?? false);
        $allowFullscreen = (bool) ($args[3] ?? true);

        $attributes = 'src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"';

        if ($title !== null && $title !== '') {
            $attributes .= ' title="' . htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($lazy) {
            $attributes .= ' loading="lazy"';
        }

        if ($allowFullscreen) {
            $attributes .= ' allowfullscreen';
        }

        return '<iframe ' . $attributes . '></iframe>';
    }
}
