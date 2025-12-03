<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate icon element (supports multiple icon libraries).
 *
 * Usage:
 * - ##icon("home")## - Default Heroicons outline
 * - ##icon("home", "solid")## - Heroicons solid
 * - ##icon("fa-home", "fontawesome")## - Font Awesome
 * - ##icon("mdi-home", "mdi")## - Material Design Icons
 * - ##icon("bi-house", "bootstrap")## - Bootstrap Icons
 */
final class IconMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'icon';
    }

    public function execute(array $args): string
    {
        $name = (string) ($args[0] ?? '');
        $variant = (string) ($args[1] ?? 'outline');
        $size = (string) ($args[2] ?? '1em');
        $class = (string) ($args[3] ?? '');

        if ($name === '') {
            return '';
        }

        return match ($variant) {
            'solid' => $this->heroiconsSolid($name, $size, $class),
            'fontawesome', 'fa' => $this->fontAwesome($name, $class),
            'mdi' => $this->materialDesign($name, $class),
            'bootstrap', 'bi' => $this->bootstrapIcon($name, $class),
            'lucide' => $this->lucide($name, $size, $class),
            default => $this->heroiconsOutline($name, $size, $class),
        };
    }

    private function heroiconsOutline(string $name, string $size, string $class): string
    {
        $cls = trim('icon icon-' . $name . ' ' . $class);

        return '<svg class="' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') . '" style="width:' . $size . ';height:' . $size . ';" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use href="#icon-' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"></use></svg>';
    }

    private function heroiconsSolid(string $name, string $size, string $class): string
    {
        $cls = trim('icon icon-solid icon-' . $name . ' ' . $class);

        return '<svg class="' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') . '" style="width:' . $size . ';height:' . $size . ';" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><use href="#icon-solid-' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"></use></svg>';
    }

    private function fontAwesome(string $name, string $class): string
    {
        $cls = trim($name . ' ' . $class);

        return '<i class="' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') . '"></i>';
    }

    private function materialDesign(string $name, string $class): string
    {
        $cls = trim($name . ' ' . $class);

        return '<i class="' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') . '"></i>';
    }

    private function bootstrapIcon(string $name, string $class): string
    {
        $cls = trim($name . ' ' . $class);

        return '<i class="' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') . '"></i>';
    }

    private function lucide(string $name, string $size, string $class): string
    {
        $cls = trim('lucide lucide-' . $name . ' ' . $class);

        return '<svg class="' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') . '" style="width:' . $size . ';height:' . $size . ';"><use href="#lucide-' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"></use></svg>';
    }
}
