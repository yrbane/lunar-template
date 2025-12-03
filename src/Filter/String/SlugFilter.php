<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\String;

use Lunar\Template\Filter\AbstractFilter;

final class SlugFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'slug';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $separator = $this->getArg($args, 0, '-');
        $str = $this->toString($value);

        // Transliterate accented characters
        $str = $this->transliterate($str);

        // Convert to lowercase
        $str = mb_strtolower($str);

        // Replace non-alphanumeric characters with separator
        $str = (string) preg_replace('/[^a-z0-9]+/', (string) $separator, $str);

        // Remove leading/trailing separators
        return trim($str, (string) $separator);
    }

    private function transliterate(string $str): string
    {
        $transliteration = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'ÿ' => 'y',
            'Œ' => 'OE', 'œ' => 'oe', 'Æ' => 'AE', 'æ' => 'ae',
            'ß' => 'ss', 'Ø' => 'O', 'ø' => 'o',
        ];

        return strtr($str, $transliteration);
    }
}
