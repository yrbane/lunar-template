<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

final class EscapeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'escape';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $strategy = (string) ($args[0] ?? 'html');

        $str = $this->toString($value);

        return match ($strategy) {
            'html' => htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'js' => $this->escapeJs($str),
            'css' => $this->escapeCss($str),
            'url' => rawurlencode($str),
            'attr' => htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            default => htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        };
    }

    private function escapeJs(string $str): string
    {
        return (string) preg_replace_callback(
            '/[^a-zA-Z0-9,._]/',
            fn ($matches) => '\\x' . dechex(\ord($matches[0])),
            $str,
        );
    }

    private function escapeCss(string $str): string
    {
        return (string) preg_replace_callback(
            '/[^a-zA-Z0-9]/',
            fn ($matches) => '\\' . dechex(\ord($matches[0])) . ' ',
            $str,
        );
    }
}
