<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Generate Schema.org JSON-LD structured data.
 *
 * Usage:
 * - ##schema("Organization", data)##
 * - ##schema("Article", data)##
 * - ##schema("Product", data)##
 * - ##schema("BreadcrumbList", breadcrumbs)##
 */
final class SchemaOrgMacro implements MacroInterface
{
    public function getName(): string
    {
        return 'schema';
    }

    public function execute(array $args): string
    {
        $type = (string) ($args[0] ?? '');
        $data = $args[1] ?? [];

        if ($type === '' || !\is_array($data)) {
            return '';
        }

        $schema = array_merge([
            '@context' => 'https://schema.org',
            '@type' => $type,
        ], $data);

        $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>';
    }
}
