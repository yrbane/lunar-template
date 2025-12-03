# API Contracts: Lunar Template Engine

**Feature**: 001-template-engine
**Date**: 2025-12-03

## Public API

### AdvancedTemplateEngine

Main entry point for all template operations.

#### Constructor

```php
__construct(
    string $templatePath,
    string $cachePath,
    ?EscaperInterface $escaper = null
)
```

**Parameters**:
- `$templatePath`: Absolute path to template directory
- `$cachePath`: Absolute path to cache directory
- `$escaper`: Optional custom escaper (defaults to HtmlEscaper)

**Throws**:
- `InvalidArgumentException`: If paths are invalid or not writable

---

#### render()

Render a template with provided data.

```php
render(string $template, array $data = []): string
```

**Parameters**:
- `$template`: Template path relative to templatePath (e.g., `'blog/article'`)
- `$data`: Associative array of variables

**Returns**: Rendered HTML string

**Throws**:
- `TemplateNotFoundException`: Template file does not exist
- `SyntaxException`: Template has syntax errors
- `CircularInheritanceException`: Circular extends detected

**Example**:
```php
$html = $engine->render('blog/article', [
    'title' => 'My Article',
    'author' => ['name' => 'John'],
]);
```

---

#### templateExists()

Check if a template file exists.

```php
templateExists(string $template): bool
```

**Parameters**:
- `$template`: Template path relative to templatePath

**Returns**: `true` if template exists, `false` otherwise

---

#### clearCache()

Clear compiled template cache.

```php
clearCache(?string $template = null): void
```

**Parameters**:
- `$template`: Optional specific template to clear; `null` clears all

**Throws**:
- `RuntimeException`: If cache directory is not writable

---

#### registerMacro()

Register a closure-based macro.

```php
registerMacro(string $name, callable $callable): void
```

**Parameters**:
- `$name`: Macro name (alphanumeric + underscore)
- `$callable`: Function to execute

**Throws**:
- `InvalidArgumentException`: Invalid macro name

**Example**:
```php
$engine->registerMacro('url', function(string $route, array $params = []): string {
    return '/route/' . $route . '?' . http_build_query($params);
});
```

---

#### registerMacroInstance()

Register a class-based macro.

```php
registerMacroInstance(MacroInterface $macro): void
```

**Parameters**:
- `$macro`: Macro instance implementing MacroInterface

---

#### loadMacrosFromDirectory()

Auto-discover and register macros from a directory.

```php
loadMacrosFromDirectory(string $namespace, string $path): void
```

**Parameters**:
- `$namespace`: PSR-4 namespace for macro classes
- `$path`: Absolute path to macro directory

**Throws**:
- `InvalidArgumentException`: Path does not exist

**Example**:
```php
$engine->loadMacrosFromDirectory(
    'App\\Template\\Macro',
    '/path/to/macros'
);
```

---

#### getRegisteredMacros()

Get list of registered macro names.

```php
getRegisteredMacros(): array
```

**Returns**: Array of macro names

---

## Interface Contracts

### MacroInterface

```php
interface MacroInterface
{
    /**
     * Get the macro name for template usage.
     */
    public function getName(): string;

    /**
     * Execute the macro with provided arguments.
     *
     * @param array $args Arguments from template call
     * @return string Rendered output (will NOT be escaped)
     */
    public function execute(array $args): string;
}
```

---

### EscaperInterface

```php
interface EscaperInterface
{
    /**
     * Escape a value for safe output.
     *
     * @param mixed $value Value to escape
     * @return string Escaped string
     */
    public function escape(mixed $value): string;
}
```

---

### CacheInterface

```php
interface CacheInterface
{
    public function get(string $key): ?string;
    public function set(string $key, string $content, int $sourceModifiedAt): void;
    public function has(string $key): bool;
    public function delete(string $key): void;
    public function clear(): void;
    public function isFresh(string $key, int $sourceModifiedAt): bool;
}
```

---

## Template Syntax Contract

### Variable Output

```
[[ variableName ]]           → Escaped output
[[ object.property ]]        → Nested access (escaped)
[[ array.0 ]]                → Array index access (escaped)
[[! rawVariable !]]          → Raw output (NOT escaped)
```

### Control Structures

```
[% if condition %]
    content
[% elseif otherCondition %]
    content
[% else %]
    content
[% endif %]

[% for item in collection %]
    [[ item ]]
[% endfor %]
```

### Template Inheritance

```
[% extends 'parent.tpl' %]

[% block blockName %]
    block content
[% endblock %]
```

### Macros

```
##macroName()##
##macroName('arg1', 'arg2')##
##macroName(variable, 'literal')##
```

---

## Error Response Contract

All exceptions extend `TemplateException` and provide:

| Property | Type | Description |
|----------|------|-------------|
| message | string | Human-readable error description |
| code | int | Error code (0 for template errors) |
| previous | ?Throwable | Chained exception if applicable |

### Exception-Specific Properties

**SyntaxException**:
- `getLine(): ?int` - Line number where error occurred
- `getTemplateFile(): string` - Template path

**CircularInheritanceException**:
- `getChain(): array` - Array of template paths forming the cycle

**TemplateNotFoundException**:
- `getTemplatePath(): string` - Requested template path
