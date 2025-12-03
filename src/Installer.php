<?php

declare(strict_types=1);

namespace Lunar\Template;

use Composer\Script\Event;

/**
 * Composer script handler for lunar-template installation.
 */
final class Installer
{
    /**
     * Copy default config file if not exists.
     */
    public static function postInstall(Event $event): void
    {
        self::copyConfig($event);
    }

    /**
     * Copy default config file if not exists.
     */
    public static function postUpdate(Event $event): void
    {
        self::copyConfig($event);
    }

    private static function copyConfig(Event $event): void
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $projectRoot = \dirname($vendorDir);

        $source = __DIR__ . '/../config/template.json.dist';
        $destination = $projectRoot . '/config/template.json';

        // Only copy if destination doesn't exist
        if (!file_exists($destination)) {
            $configDir = \dirname($destination);

            if (!is_dir($configDir)) {
                mkdir($configDir, 0o755, true);
            }

            if (copy($source, $destination)) {
                $event->getIO()->write('<info>lunar-template:</info> Created config/template.json');
            }
        }
    }
}
