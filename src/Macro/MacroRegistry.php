<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

use Lunar\Template\Exception\TemplateException;

/**
 * Registry for template macros.
 */
class MacroRegistry
{
    /** @var array<string, callable> */
    private array $macros = [];

    /**
     * Register a macro by name.
     */
    public function register(string $name, callable $callback): self
    {
        $this->macros[$name] = $callback;

        return $this;
    }

    /**
     * Register a macro from an interface instance.
     */
    public function registerInstance(MacroInterface $macro): self
    {
        return $this->register($macro->getName(), [$macro, 'execute']);
    }

    /**
     * Check if a macro is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->macros[$name]);
    }

    /**
     * Get a macro callback.
     */
    public function get(string $name): callable
    {
        if (!$this->has($name)) {
            throw TemplateException::macroNotFound($name);
        }

        return $this->macros[$name];
    }

    /**
     * Call a macro with arguments.
     *
     * @param string $name Macro name
     * @param array<int, mixed> $args Arguments
     *
     * @return mixed Result
     */
    public function call(string $name, array $args): mixed
    {
        $callback = $this->get($name);

        if (\is_array($callback) && isset($callback[0]) && $callback[0] instanceof MacroInterface) {
            return $callback[0]->execute($args);
        }

        return $callback(...$args);
    }

    /**
     * Get all registered macro names.
     *
     * @return array<string>
     */
    public function getNames(): array
    {
        return array_keys($this->macros);
    }

    /**
     * Remove a macro.
     */
    public function remove(string $name): self
    {
        unset($this->macros[$name]);

        return $this;
    }

    /**
     * Clear all macros.
     */
    public function clear(): self
    {
        $this->macros = [];

        return $this;
    }

    /**
     * Get count of registered macros.
     */
    public function count(): int
    {
        return \count($this->macros);
    }
}
