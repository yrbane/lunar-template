<?php

declare(strict_types=1);

namespace Lunar\Template\Filter\Html;

use Lunar\Template\Filter\AbstractFilter;

/**
 * Create a badge/tag element (span with badge class).
 *
 * Usage: [[ "New" | badge ]]              -> <span class="badge">New</span>
 * Usage: [[ "5" | badge("danger") ]]      -> <span class="badge badge-danger">5</span>
 * Usage: [[ status | badge("success") ]]  -> <span class="badge badge-success">status</span>
 */
final class BadgeFilter extends AbstractFilter
{
    public function getName(): string
    {
        return 'badge';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $content = $this->toString($value);
        $variant = $args[0] ?? null;

        $class = 'badge';
        if ($variant !== null && $variant !== '') {
            $class .= ' badge-' . htmlspecialchars((string) $variant, ENT_QUOTES, 'UTF-8');
        }

        return '<span class="' . $class . '">' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') . '</span>';
    }
}
