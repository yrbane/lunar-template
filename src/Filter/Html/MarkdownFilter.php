<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Convert Markdown syntax to HTML.
 *
 * Stratégie :
 *  - Si `league/commonmark` est installé, on l'utilise (parser CommonMark
 *    complet : tables, autolinks, footnotes via extensions, etc.).
 *  - Sinon, fallback sur une implémentation regex maison qui couvre :
 *    gras, italique, barré, code (inline + blocs fenced), liens, images,
 *    titres, blockquotes, hr, listes ordonnées/non, autolinks bruts.
 *
 * Pour activer CommonMark :
 *   composer require league/commonmark
 */
final class MarkdownFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'markdown';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $text = $this->toString($value);

        if ($text === '') {
            return '';
        }

        if (class_exists('League\\CommonMark\\CommonMarkConverter')) {
            return $this->applyWithCommonMark($text);
        }

        return $this->applyWithRegex($text);
    }

    /**
     * Conversion via league/commonmark si la lib est installée.
     */
    /**
     * @phpstan-ignore-next-line — la classe CommonMark est optionnelle.
     */
    private function applyWithCommonMark(string $text): string
    {
        /** @var class-string $converterClass */
        $converterClass = 'League\\CommonMark\\CommonMarkConverter';
        $converter = new $converterClass();

        // L'API de commonmark v2 expose convert() qui retourne un objet
        // RenderedContentInterface avec __toString().
        return (string) $converter->convert($text); // @phpstan-ignore-line
    }

    private function applyWithRegex(string $text): string
    {
        // Process in order to avoid conflicts
        $text = $this->convertCodeBlocks($text);
        $text = $this->convertInlineCode($text);
        $text = $this->convertImages($text);
        $text = $this->convertLinks($text);
        $text = $this->convertHeaders($text);
        $text = $this->convertBold($text);
        $text = $this->convertItalic($text);
        $text = $this->convertStrikethrough($text);
        $text = $this->convertBlockquotes($text);
        $text = $this->convertHorizontalRules($text);
        $text = $this->convertUnorderedLists($text);
        $text = $this->convertOrderedLists($text);
        $text = $this->convertAutolinks($text);

        return $text;
    }

    private function convertCodeBlocks(string $text): string
    {
        // Fenced code blocks ```code```
        return (string) preg_replace(
            '/```(\w*)\n([\s\S]*?)```/',
            '<pre><code class="language-$1">$2</code></pre>',
            $text,
        );
    }

    private function convertInlineCode(string $text): string
    {
        return (string) preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    }

    private function convertImages(string $text): string
    {
        return (string) preg_replace(
            '/!\[([^\]]*)\]\(([^)]+)\)/',
            '<img src="$2" alt="$1">',
            $text,
        );
    }

    private function convertLinks(string $text): string
    {
        return (string) preg_replace(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            '<a href="$2">$1</a>',
            $text,
        );
    }

    private function convertHeaders(string $text): string
    {
        $text = (string) preg_replace('/^###### (.+)$/m', '<h6>$1</h6>', $text);
        $text = (string) preg_replace('/^##### (.+)$/m', '<h5>$1</h5>', $text);
        $text = (string) preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $text);
        $text = (string) preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
        $text = (string) preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
        $text = (string) preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);

        return $text;
    }

    private function convertBold(string $text): string
    {
        $text = (string) preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
        $text = (string) preg_replace('/__([^_]+)__/', '<strong>$1</strong>', $text);

        return $text;
    }

    private function convertItalic(string $text): string
    {
        $text = (string) preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
        $text = (string) preg_replace('/_([^_]+)_/', '<em>$1</em>', $text);

        return $text;
    }

    private function convertStrikethrough(string $text): string
    {
        return (string) preg_replace('/~~([^~]+)~~/', '<del>$1</del>', $text);
    }

    private function convertBlockquotes(string $text): string
    {
        return (string) preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $text);
    }

    private function convertHorizontalRules(string $text): string
    {
        return (string) preg_replace('/^[-*_]{3,}$/m', '<hr>', $text);
    }

    private function convertUnorderedLists(string $text): string
    {
        // Simple unordered list conversion
        return (string) preg_replace_callback(
            '/(?:^[*\-+] .+$\n?)+/m',
            function (array $matches): string {
                $items = preg_split('/\n/', trim($matches[0]));
                if ($items === false) {
                    return $matches[0];
                }
                $listItems = array_map(
                    fn (string $item): string => '<li>' . preg_replace('/^[*\-+] /', '', $item) . '</li>',
                    $items,
                );

                return '<ul>' . implode('', $listItems) . '</ul>';
            },
            $text,
        );
    }

    /**
     * Convertit les URLs nues en liens cliquables, hors balises HTML
     * déjà créées (href="…") et hors blocs <code>/<pre>.
     */
    private function convertAutolinks(string $text): string
    {
        // Protège les attributs href, src et le contenu des balises <code>/<pre>
        $placeholders = [];
        $idx = 0;
        $protect = function (string $match) use (&$placeholders, &$idx): string {
            $key = "\x00LUNAR_PROTECT_{$idx}\x00";
            $placeholders[$key] = $match;
            $idx++;

            return $key;
        };

        $protected = (string) preg_replace_callback(
            '#(?:<a\b[^>]*>.*?</a>|<code\b[^>]*>.*?</code>|<pre\b[^>]*>.*?</pre>|href="[^"]*"|src="[^"]*")#is',
            static fn (array $m) => $protect($m[0]),
            $text,
        );

        // Autolink des URLs http(s) et www.
        $linked = (string) preg_replace(
            '#(?<![">])\b((?:https?://|www\.)[^\s<]+)#i',
            '<a href="$1">$1</a>',
            $protected,
        );

        // www.* sans schéma → préfixer http://
        $linked = (string) preg_replace(
            '#<a href="(www\.[^"]+)">#i',
            '<a href="http://$1">',
            $linked,
        );

        return strtr($linked, $placeholders);
    }

    private function convertOrderedLists(string $text): string
    {
        // Simple ordered list conversion
        return (string) preg_replace_callback(
            '/(?:^\d+\. .+$\n?)+/m',
            function (array $matches): string {
                $items = preg_split('/\n/', trim($matches[0]));
                if ($items === false) {
                    return $matches[0];
                }
                $listItems = array_map(
                    fn (string $item): string => '<li>' . preg_replace('/^\d+\. /', '', $item) . '</li>',
                    $items,
                );

                return '<ol>' . implode('', $listItems) . '</ol>';
            },
            $text,
        );
    }
}
