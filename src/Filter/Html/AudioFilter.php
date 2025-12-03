<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create an <audio> element from a URL.
 *
 * Usage: [[ url | audio ]]                    -> <audio src="url" controls></audio>
 * Usage: [[ url | audio(true) ]]              -> <audio src="url" controls autoplay></audio>
 * Usage: [[ url | audio(false, true) ]]       -> <audio src="url" controls loop></audio>
 * Usage: [[ url | audio(false, false, "player") ]] -> <audio src="url" controls class="player"></audio>
 */
final class AudioFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'audio';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $src = $this->toString($value);

        if ($src === '') {
            return '';
        }

        $autoplay = (bool) ($args[0] ?? false);
        $loop = (bool) ($args[1] ?? false);
        $class = $args[2] ?? null;

        $attributes = 'src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"';
        $attributes .= ' controls';

        if ($autoplay) {
            $attributes .= ' autoplay';
        }

        if ($loop) {
            $attributes .= ' loop';
        }

        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<audio ' . $attributes . '></audio>';
    }
}
