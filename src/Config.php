<?php
/**
 * Configuration helper for lunar-template.
 *
 * @since 1.0.0
 * @author seb@nethttp.net
 */
declare(strict_types=1);

namespace Lunar\Template;

use Lunar\Config\Config as BaseConfig;

/**
 * Class Config.
 *
 * Convenience wrapper around Lunar\Config\Config for template-specific settings.
 * Reads from config/template.json.
 */
final class Config
{
    private const CONFIG_FILE = 'template';

    /**
     * Get a configuration value.
     *
     * @param string $key     Dot notation key (e.g., 'template.template_path')
     * @param mixed  $default Default value if key not found
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return BaseConfig::get(self::CONFIG_FILE, $key, $default);
    }

    /**
     * Get the template directory path.
     */
    public static function getTemplatePath(): string
    {
        $path = self::get('template.template_path', 'templates');

        return BaseConfig::resolvePath($path);
    }

    /**
     * Get the cache directory path.
     */
    public static function getCachePath(): string
    {
        $path = self::get('template.cache_path', 'cache/template');

        return BaseConfig::resolvePath('cache/' . $path);
    }

    /**
     * Get the template file extension.
     */
    public static function getExtension(): string
    {
        return self::get('template.extension', 'tpl');
    }

    /**
     * Get the project root directory.
     */
    public static function getProjectRoot(): string
    {
        return BaseConfig::getProjectRoot();
    }

    /**
     * Reset the configuration (useful for testing).
     */
    public static function reset(): void
    {
        BaseConfig::reset();
    }
}
