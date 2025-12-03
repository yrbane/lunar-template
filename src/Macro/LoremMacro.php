<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate Lorem Ipsum placeholder text.
 *
 * Usage:
 * - ##lorem()## - 1 paragraph
 * - ##lorem(3)## - 3 paragraphs
 * - ##lorem("words", 10)## - 10 words
 * - ##lorem("sentences", 5)## - 5 sentences
 */
final class LoremMacro implements MacroInterface
{
    private const array WORDS = [
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
        'sed', 'do', 'eiusmod', 'tempor', 'incididunt', 'ut', 'labore', 'et', 'dolore',
        'magna', 'aliqua', 'enim', 'ad', 'minim', 'veniam', 'quis', 'nostrud',
        'exercitation', 'ullamco', 'laboris', 'nisi', 'aliquip', 'ex', 'ea', 'commodo',
        'consequat', 'duis', 'aute', 'irure', 'in', 'reprehenderit', 'voluptate',
        'velit', 'esse', 'cillum', 'fugiat', 'nulla', 'pariatur', 'excepteur', 'sint',
        'occaecat', 'cupidatat', 'non', 'proident', 'sunt', 'culpa', 'qui', 'officia',
        'deserunt', 'mollit', 'anim', 'id', 'est', 'laborum', 'perspiciatis', 'unde',
        'omnis', 'iste', 'natus', 'error', 'voluptatem', 'accusantium', 'doloremque',
        'laudantium', 'totam', 'rem', 'aperiam', 'eaque', 'ipsa', 'quae', 'ab', 'illo',
        'inventore', 'veritatis', 'quasi', 'architecto', 'beatae', 'vitae', 'dicta',
    ];

    public function getName(): string
    {
        return 'lorem';
    }

    public function execute(array $args): string
    {
        $first = $args[0] ?? 1;

        if (\is_string($first)) {
            $count = (int) ($args[1] ?? 10);

            return match ($first) {
                'words' => $this->words($count),
                'sentences' => $this->sentences($count),
                'paragraphs' => $this->paragraphs($count),
                default => $this->paragraphs(1),
            };
        }

        return $this->paragraphs((int) $first);
    }

    private function words(int $count): string
    {
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = self::WORDS[array_rand(self::WORDS)];
        }

        return ucfirst(implode(' ', $result));
    }

    private function sentences(int $count): string
    {
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $wordCount = random_int(8, 15);
            $sentence = $this->words($wordCount);
            $result[] = ucfirst($sentence) . '.';
        }

        return implode(' ', $result);
    }

    private function paragraphs(int $count): string
    {
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $sentenceCount = random_int(4, 8);
            $result[] = $this->sentences($sentenceCount);
        }

        return implode("\n\n", $result);
    }
}
