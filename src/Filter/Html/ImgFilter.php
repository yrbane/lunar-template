<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create an <img> element from a URL.
 *
 * Usage: [[ url | img ]]                      -> <img src="url" alt="">
 * Usage: [[ url | img("Alt text") ]]          -> <img src="url" alt="Alt text">
 * Usage: [[ url | img("Alt", "photo") ]]      -> <img src="url" alt="Alt" class="photo">
 * Usage: [[ url | img("Alt", "photo", true) ]]-> <img src="url" alt="Alt" class="photo" loading="lazy">
 */
final class ImgFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'img';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $src = $this->toString($value);

        if ($src === '') {
            return '';
        }

        $alt = (string) ($args[0] ?? '');
        $class = $args[1] ?? null;
        $lazy = (bool) ($args[2] ?? false);

        $attributes = 'src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"';
        $attributes .= ' alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"';

        if ($class !== null && $class !== '') {
            $attributes .= ' class="' . htmlspecialchars((string) $class, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($lazy) {
            $attributes .= ' loading="lazy"';
        }

        return '<img ' . $attributes . '>';
    }
}
