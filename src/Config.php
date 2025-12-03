<?php
/**
 * Configuration loader for lunar-template.
 *
 * @since 1.0.0
 * @author seb@nethttp.net
 */
declare(strict_types=1);

namespace Lunar\Template;

/**
 * Class Config.
 *
 * Reads configuration from config/template.json in the project root.
 */
final class Config
{
    private const CONFIG_PATH = 'config/template.json';

    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /** @var string|null */
    private static ?string $projectRoot = null;

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
        self::load();

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Get the template directory path.
     */
    public static function getTemplatePath(): string
    {
        $path = self::get('template.template_path', 'templates');

        // If relative path, prepend project root
        if (!str_starts_with($path, '/')) {
            $path = self::getProjectRoot() . '/' . $path;
        }

        return $path;
    }

    /**
     * Get the cache directory path.
     */
    public static function getCachePath(): string
    {
        $path = self::get('template.cache_path', 'cache/template');

        // If relative path, prepend project root
        if (!str_starts_with($path, '/')) {
            $path = self::getProjectRoot() . '/cache/' . $path;
        }

        return $path;
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
        if (null !== self::$projectRoot) {
            return self::$projectRoot;
        }

        // Use PROJECT_ROOT constant if defined
        if (defined('PROJECT_ROOT')) {
            self::$projectRoot = PROJECT_ROOT;
            return self::$projectRoot;
        }

        // Otherwise, detect from cwd
        $dir = getcwd() ?: '.';

        while ($dir !== '/') {
            if (file_exists($dir . '/composer.json')) {
                self::$projectRoot = $dir;
                return self::$projectRoot;
            }
            $dir = dirname($dir);
        }

        self::$projectRoot = getcwd() ?: '.';
        return self::$projectRoot;
    }

    /**
     * Load configuration from file.
     */
    private static function load(): void
    {
        if (null !== self::$config) {
            return;
        }

        $configPath = self::getProjectRoot() . '/' . self::CONFIG_PATH;

        if (!file_exists($configPath)) {
            self::$config = [];
            return;
        }

        $content = file_get_contents($configPath);

        if (false === $content) {
            self::$config = [];
            return;
        }

        $config = json_decode($content, true);

        self::$config = is_array($config) ? $config : [];
    }

    /**
     * Reset the configuration (useful for testing).
     */
    public static function reset(): void
    {
        self::$config = null;
        self::$projectRoot = null;
    }
}
