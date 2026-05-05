<?php

declare(strict_types=1);

namespace Lunar\Template\Linter;

/**
 * Une erreur ou un avertissement détecté par le linter.
 */
final readonly class LintIssue
{
    /**
     * @param string $type Type d'issue (unclosed_block, orphan_close, unclosed_token, ...)
     * @param int $line Ligne du source (1-indexée)
     * @param string $message Description humaine
     * @param string $file Chemin du fichier (vide si lint à la volée)
     * @param string $severity error | warning | info
     */
    public function __construct(
        public string $type,
        public int $line,
        public string $message,
        public string $file = '',
        public string $severity = 'error',
    ) {
    }
}
