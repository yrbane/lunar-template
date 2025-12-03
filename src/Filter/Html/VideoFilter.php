<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create a <video> element from a URL.
 *
 * Usage: [[ url | video ]]                          -> <video src="url" controls></video>
 * Usage: [[ url | video(true) ]]                    -> <video src="url" controls autoplay></video>
 * Usage: [[ url | video(false, true) ]]             -> <video src="url" controls loop></video>
 * Usage: [[ url | video(false, false, true) ]]      -> <video src="url" controls muted></video>
 * Usage: [[ url | video(false, false, false, "player") ]] -> <video src="url" controls class="player"></video>
 */
final class VideoFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'video';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $src = $this->toString($value);

        if ($src === '') {
            return '';
        }

        $autoplay = (bool) ($args[0] ?? false);
        $loop = (bool) ($args[1] ?? false);
        $muted = (bool) ($args[2] ?? false);
        $class = $args[3] ?? null;
        $poster = $args[4] ?? null;

        $attributes = 'src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"';
        $attributes .= ' controls';

        if ($autoplay) {
            $attributes .= ' autoplay';
        }

        if ($loop) {
            $attributes .= ' loop';
        }

        if ($muted) {
            $attributes .= ' muted';
        }

        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($poster !== null && $poster !== '') {
            $attributes .= ' poster="' . htmlspecialchars((string) $poster, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<video ' . $attributes . '></video>';
    }
}
