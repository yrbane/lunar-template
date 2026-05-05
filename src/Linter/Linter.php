<?php

declare(strict_types=1);

namespace Lunar\Template\Linter;

/**
 * Linter statique pour les templates Lunar.
 *
 * Analyse un source .tpl sans l'exécuter et signale :
 *  - les blocs non fermés ([% if %] sans [% endif %], etc.)
 *  - les fermetures orphelines ([% endif %] sans [% if %])
 *  - les tokens non fermés ([[ var sans ]], [# .. sans #])
 *  - les paires mal appariées ([% if %] suivi de [% endfor %])
 */
final class Linter
{
    /** @var array<string, string> Mapping ouvre → ferme attendu */
    private const BLOCK_PAIRS = [
        'if' => 'endif',
        'for' => 'endfor',
        'block' => 'endblock',
    ];

    /**
     * @return list<LintIssue>
     */
    public function lint(string $source, string $file = ''): array
    {
        $issues = [];

        $this->checkUnclosedTokens($source, $file, $issues);
        $this->checkBlockBalance($source, $file, $issues);

        return $issues;
    }

    /**
     * Détecte les tokens [[ ]], [[! !]], [# #] non fermés.
     *
     * @param list<LintIssue> $issues
     */
    private function checkUnclosedTokens(string $source, string $file, array &$issues): void
    {
        // Un linter pragmatique : on retire d'abord les tokens correctement fermés
        // puis on cherche des ouvertures résiduelles.
        $stripped = $source;
        $stripped = (string) preg_replace('/\[\[!.*?!\]\]/s', '', $stripped);
        $stripped = (string) preg_replace('/\[\[.*?\]\]/s', '', $stripped);
        $stripped = (string) preg_replace('/\[#.*?#\]/s', '', $stripped);
        $stripped = (string) preg_replace('/\[%.*?%\]/s', '', $stripped);

        $patterns = [
            '/\[\[/' => ['type' => 'unclosed_token', 'msg' => 'Variable non fermée [[ ... ]]'],
            '/\[#/' => ['type' => 'unclosed_token', 'msg' => 'Commentaire non fermé [# ... #]'],
            '/\[%/' => ['type' => 'unclosed_token', 'msg' => 'Tag non fermé [% ... %]'],
        ];

        foreach ($patterns as $regex => $info) {
            if (preg_match($regex, $stripped, $m, PREG_OFFSET_CAPTURE)) {
                $offset = $m[0][1];
                $line = substr_count(substr($stripped, 0, $offset), "\n") + 1;
                $issues[] = new LintIssue(
                    type: $info['type'],
                    line: $line,
                    message: $info['msg'],
                    file: $file,
                );
            }
        }
    }

    /**
     * Détecte les déséquilibres de blocs [% if/for/block %].
     *
     * @param list<LintIssue> $issues
     */
    private function checkBlockBalance(string $source, string $file, array &$issues): void
    {
        // Capture chaque ouverture/fermeture avec sa ligne.
        $stack = []; // pile de [keyword, line]

        if (preg_match_all('/\[%\s*(\w+)\b.*?%\]/s', $source, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $i => $match) {
                $keyword = $match[0];
                $offset = $matches[0][$i][1];
                $line = substr_count(substr($source, 0, $offset), "\n") + 1;

                if (\array_key_exists($keyword, self::BLOCK_PAIRS)) {
                    $stack[] = [$keyword, $line];
                    continue;
                }

                // Closing token ?
                $matchedOpen = array_search($keyword, self::BLOCK_PAIRS, true);
                if ($matchedOpen !== false) {
                    if (\count($stack) === 0) {
                        $issues[] = new LintIssue(
                            type: 'orphan_close',
                            line: $line,
                            message: "[% $keyword %] sans ouverture correspondante",
                            file: $file,
                        );
                        continue;
                    }

                    $top = array_pop($stack);
                    if ($top[0] !== $matchedOpen) {
                        $issues[] = new LintIssue(
                            type: 'mismatched_block',
                            line: $line,
                            message: "[% $keyword %] ne ferme pas [% {$top[0]} %] (ligne {$top[1]})",
                            file: $file,
                        );
                    }
                }
            }
        }

        // Restant sur la pile = blocs non fermés
        foreach ($stack as [$keyword, $line]) {
            $issues[] = new LintIssue(
                type: 'unclosed_block',
                line: $line,
                message: "[% $keyword %] non fermé",
                file: $file,
            );
        }
    }
}
