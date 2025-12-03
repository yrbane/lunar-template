<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate and manipulate colors.
 *
 * Usage:
 * - ##color("primary")## - CSS variable
 * - ##color("#ff0000", "lighten", 20)## - Lighten by 20%
 * - ##color("#ff0000", "darken", 20)## - Darken by 20%
 * - ##color("#ff0000", "alpha", 0.5)## - With alpha
 * - ##color("random")## - Random color
 * - ##color("#ff0000", "rgb")## - Convert to RGB
 */
final class ColorMacro implements MacroInterface
{
    private const array PALETTE = [
        'primary' => '#4F46E5',
        'secondary' => '#7C3AED',
        'success' => '#10B981',
        'danger' => '#EF4444',
        'warning' => '#F59E0B',
        'info' => '#3B82F6',
        'light' => '#F3F4F6',
        'dark' => '#1F2937',
        'white' => '#FFFFFF',
        'black' => '#000000',
    ];

    public function getName(): string
    {
        return 'color';
    }

    public function execute(array $args): string
    {
        $color = (string) ($args[0] ?? '');
        $operation = (string) ($args[1] ?? '');
        $value = $args[2] ?? 0;

        if ($color === 'random') {
            return \sprintf('#%06X', random_int(0, 0xFFFFFF));
        }

        // Named color
        if (isset(self::PALETTE[$color])) {
            $color = self::PALETTE[$color];
        }

        // CSS variable
        if (!str_starts_with($color, '#') && !str_starts_with($color, 'rgb')) {
            return 'var(--' . $color . ')';
        }

        if ($operation === '') {
            return $color;
        }

        $rgb = $this->hexToRgb($color);
        if ($rgb === null) {
            return $color;
        }

        return match ($operation) {
            'lighten' => $this->lighten($rgb, (float) $value),
            'darken' => $this->darken($rgb, (float) $value),
            'alpha', 'opacity' => $this->withAlpha($rgb, (float) $value),
            'rgb' => \sprintf('rgb(%d, %d, %d)', $rgb['r'], $rgb['g'], $rgb['b']),
            'hsl' => $this->toHsl($rgb),
            default => $color,
        };
    }

    /**
     * @return array{r: int, g: int, b: int}|null
     */
    private function hexToRgb(string $hex): ?array
    {
        $hex = ltrim($hex, '#');

        if (\strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (\strlen($hex) !== 6) {
            return null;
        }

        return [
            'r' => (int) hexdec(substr($hex, 0, 2)),
            'g' => (int) hexdec(substr($hex, 2, 2)),
            'b' => (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * @param array{r: int, g: int, b: int} $rgb
     */
    private function lighten(array $rgb, float $percent): string
    {
        $factor = 1 + ($percent / 100);

        return \sprintf(
            '#%02X%02X%02X',
            min(255, (int) ($rgb['r'] * $factor)),
            min(255, (int) ($rgb['g'] * $factor)),
            min(255, (int) ($rgb['b'] * $factor)),
        );
    }

    /**
     * @param array{r: int, g: int, b: int} $rgb
     */
    private function darken(array $rgb, float $percent): string
    {
        $factor = 1 - ($percent / 100);

        return \sprintf(
            '#%02X%02X%02X',
            max(0, (int) ($rgb['r'] * $factor)),
            max(0, (int) ($rgb['g'] * $factor)),
            max(0, (int) ($rgb['b'] * $factor)),
        );
    }

    /**
     * @param array{r: int, g: int, b: int} $rgb
     */
    private function withAlpha(array $rgb, float $alpha): string
    {
        $alpha = max(0, min(1, $alpha));

        return \sprintf('rgba(%d, %d, %d, %s)', $rgb['r'], $rgb['g'], $rgb['b'], $alpha);
    }

    /**
     * @param array{r: int, g: int, b: int} $rgb
     */
    private function toHsl(array $rgb): string
    {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            $h = match ($max) {
                $r => (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6,
                $g => (($b - $r) / $d + 2) / 6,
                default => (($r - $g) / $d + 4) / 6,
            };
        }

        return \sprintf('hsl(%d, %d%%, %d%%)', (int) ($h * 360), (int) ($s * 100), (int) ($l * 100));
    }
}
