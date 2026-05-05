<?php

declare(strict_types=1);

namespace Lunar\Template\Runtime;

/**
 * Résolution d'une ligne du fichier compilé vers son emplacement
 * d'origine dans le template source.
 *
 * Repose sur des marqueurs PHP `<?php /* L:file:line *\/ ?>` insérés
 * en tête de chaque ligne du source au moment de la compilation. Ces
 * marqueurs sont consommés silencieusement par PHP à l'exécution
 * (aucun impact sur la sortie HTML) mais lisibles depuis le fichier
 * compilé.
 */
final class SourceMap
{
    /**
     * Préfixe le source par des marqueurs ligne-à-ligne.
     *
     * Le format `<?php /* L:file:N *\/ ?>` est consommé silencieusement
     * par PHP à l'exécution (le \n suivant un `?>` est strippé), donc
     * pas d'impact sur la sortie ni sur la numérotation côté compilé.
     */
    public static function inject(string $source, string $file): string
    {
        $lines = explode("\n", $source);
        foreach ($lines as $i => $line) {
            $marker = '<?php /* L:' . $file . ':' . ($i + 1) . ' */ ?>';
            $lines[$i] = $marker . $line;
        }

        return implode("\n", $lines);
    }

    /**
     * Résout une ligne du fichier compilé vers (file, line) d'origine.
     *
     * @return array{file: string, line: int}|null
     */
    public static function resolve(string $compiledFile, int $compiledLine): ?array
    {
        if (!file_exists($compiledFile)) {
            return null;
        }

        $lines = file($compiledFile, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return null;
        }

        // Marche vers le haut depuis la ligne d'erreur, retourne le marqueur
        // le plus proche.
        $end = min($compiledLine - 1, \count($lines) - 1);
        for ($i = $end; $i >= 0; $i--) {
            if (preg_match('#<\?php /\* L:(.+?):(\d+) \*/ \?>#', $lines[$i], $m)) {
                return ['file' => $m[1], 'line' => (int) $m[2]];
            }
        }

        return null;
    }
}
