# Lunar Template Engine

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)]()
[![PHPStan](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg)]()

[Français](README.fr.md)

**Lunar Template Engine** is a standalone, advanced template engine for PHP 8.3+ featuring template inheritance, blocks, macros, and intelligent caching.

## Features

- **Template Inheritance** - Extend templates with `[% extends 'parent.tpl' %]`
- **Multi-level Inheritance** - Chain multiple template levels with `[% parent %]` support
- **Block System** - Override parent blocks with `[% block content %]`
- **Macros** - Reusable components with `##macroName(args)##`
- **Directives** - `[% set %]` and `[% include %]` for advanced control
- **Smart Caching** - Automatic compilation and caching with prewarming support
- **Secure** - XSS protection with automatic HTML escaping and path validation
- **Clean Syntax** - Intuitive template syntax
- **Modular Architecture** - Parser, Compiler, Renderer, and Cache components
- **Standalone** - No dependencies, framework agnostic
- **100% Test Coverage** - Fully tested with PHPStan level 7

## Installation

```bash
composer require lunar/template
```

## Quick Start

### Using AdvancedTemplateEngine (All-in-one)

```php
<?php
use Lunar\Template\AdvancedTemplateEngine;

$engine = new AdvancedTemplateEngine(
    templatePath: '/path/to/templates',
    cachePath: '/path/to/cache'
);

$html = $engine->render('blog/article', [
    'title' => 'My Article',
    'content' => 'Article content...',
    'author' => 'John Doe'
]);

echo $html;
```

### Using Modular Components

```php
<?php
use Lunar\Template\Renderer\TemplateRenderer;
use Lunar\Template\Cache\FilesystemCache;
use Lunar\Template\Cache\CacheWarmer;

// Create renderer with cache
$renderer = new TemplateRenderer(
    templatePath: '/path/to/templates',
    cachePath: '/path/to/cache'
);

// Set default variables
$renderer->setDefaultVariables([
    'siteName' => 'My Website',
    'currentYear' => date('Y')
]);

// Register macros
$renderer->registerMacro('uppercase', fn(string $text) => strtoupper($text));

// Render template
$html = $renderer->render('page', ['title' => 'Home']);

// Prewarm cache
$cache = new FilesystemCache('/path/to/cache');
$warmer = new CacheWarmer('/path/to/templates', $cache);
$warmer->warmRecursive(); // Precompile all templates
```

## Template Syntax

### Variables

```html
<!-- Simple variable (auto-escaped) -->
<h1>[[ title ]]</h1>

<!-- Object property / dot notation -->
<p>By [[ author.name ]]</p>

<!-- Array access -->
<span>[[ tags.0 ]]</span>

<!-- Nested access -->
<p>[[ user.profile.email ]]</p>
```

### Conditions

```html
[% if user.isLoggedIn %]
    <p>Welcome, [[ user.name ]]!</p>
[% elseif user.isGuest %]
    <p>Hello, guest!</p>
[% else %]
    <p>Please log in.</p>
[% endif %]

<!-- With comparison operators -->
[% if count > 0 %]
    <p>You have [[ count ]] items.</p>
[% endif %]

[% if status == "active" %]
    <span class="active">Active</span>
[% endif %]
```

### Loops

```html
[% for article in articles %]
    <article>
        <h2>[[ article.title ]]</h2>
        <p>[[ article.excerpt ]]</p>
    </article>
[% endfor %]
```

### Variable Assignment

```html
[% set pageTitle = "My Page" %]
[% set count = 42 %]
[% set isActive = true %]
[% set userName = user.name %]

<h1>[[ pageTitle ]]</h1>
```

### Template Inclusion

```html
<!-- Simple include -->
[% include 'partials/header.tpl' %]

<!-- Include with variables -->
[% include 'components/card.tpl' with {title: "Card Title", count: 5} %]

<!-- Include with dynamic template -->
[% include templateName %]
```

### Template Inheritance

**Base template (`base.html.tpl`):**
```html
<!DOCTYPE html>
<html>
<head>
    <title>[% block title %]Default Title[% endblock %]</title>
</head>
<body>
    <header>
        [% block header %]
            <h1>My Website</h1>
        [% endblock %]
    </header>

    <main>
        [% block content %]
            Default content
        [% endblock %]
    </main>

    <footer>
        [% block footer %]
            <p>&copy; 2025 My Website</p>
        [% endblock %]
    </footer>
</body>
</html>
```

**Child template (`article.html.tpl`):**
```html
[% extends 'base.html.tpl' %]

[% block title %][[ article.title ]] - My Blog[% endblock %]

[% block content %]
    <article>
        <h1>[[ article.title ]]</h1>
        <time>[[ article.publishedAt ]]</time>
        <div>[[ article.content ]]</div>
    </article>
[% endblock %]
```

### Multi-level Inheritance with Parent Block

```html
<!-- grandparent.tpl -->
<div class="container">
    [% block content %]Base Content[% endblock %]
</div>

<!-- parent.tpl -->
[% extends 'grandparent.tpl' %]
[% block content %]
    <div class="wrapper">
        [% parent %]  <!-- Includes "Base Content" -->
    </div>
[% endblock %]

<!-- child.tpl -->
[% extends 'parent.tpl' %]
[% block content %]
    <h1>Title</h1>
    [% parent %]  <!-- Includes wrapped parent content -->
[% endblock %]
```

### Macros

**Register a macro:**
```php
$engine->registerMacro('url', function(string $routeName, array $params = []) {
    return "/route/{$routeName}?" . http_build_query($params);
});
```

**Use in template:**
```html
<a href="##url('blog.show', ['id' => article.id])##">Read more</a>
```

**Built-in macros:**
```html
<!-- Asset URLs -->
<link rel="stylesheet" href="##asset('/css/app.css')##">

<!-- Date formatting -->
<time>##date('F j, Y')##</time>
<time>##date('Y-m-d', article.publishedAt)##</time>
```

## Architecture

### Components

| Component | Description |
|-----------|-------------|
| `TemplateParser` | Parses template source into structured components |
| `TemplateCompiler` | Compiles template syntax to PHP code |
| `TemplateRenderer` | Renders templates with variable injection |
| `InheritanceResolver` | Handles multi-level template inheritance |
| `FilesystemCache` | File-based template cache |
| `CacheWarmer` | Precompiles templates for production |
| `MacroRegistry` | Centralized macro management |
| `PathValidator` | Security: prevents directory traversal |
| `HtmlEscaper` | Security: XSS protection |

### Exceptions

```php
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Exception\TemplateNotFoundException;
use Lunar\Template\Exception\SyntaxException;
use Lunar\Template\Exception\CircularInheritanceException;
use Lunar\Template\Exception\MacroNotFoundException;

try {
    $html = $engine->render('blog/article', $data);
} catch (TemplateNotFoundException $e) {
    // Template file does not exist
} catch (SyntaxException $e) {
    // Template has syntax errors
    echo "Error at line " . $e->getLineNumber();
} catch (CircularInheritanceException $e) {
    // Circular extends detected (e.g., A extends B extends A)
    echo "Chain: " . implode(' -> ', $e->getInheritanceChain());
} catch (MacroNotFoundException $e) {
    // Macro not registered
    echo "Unknown macro: " . $e->getMacroName();
}
```

## Advanced Usage

### Custom Macro Classes

```php
<?php
use Lunar\Template\Macro\MacroInterface;

class UrlMacro implements MacroInterface
{
    public function __construct(private RouterInterface $router) {}

    public function getName(): string
    {
        return 'url';
    }

    public function execute(array $args): string
    {
        $routeName = $args[0] ?? '';
        return $this->router->generateUrl($routeName);
    }
}

$engine->registerMacroInstance(new UrlMacro($router));
```

### Macro Registry

```php
use Lunar\Template\Macro\MacroRegistry;
use Lunar\Template\Macro\DateMacro;

$registry = new MacroRegistry();
$registry
    ->register('greet', fn(string $name) => "Hello, $name!")
    ->registerInstance(new DateMacro('Y-m-d', 'Europe/Paris'));

// Check if macro exists
if ($registry->has('greet')) {
    $result = $registry->call('greet', ['World']);
}
```

### Custom Directives

```php
use Lunar\Template\Compiler\Directive\DirectiveInterface;

class UppercaseDirective implements DirectiveInterface
{
    public function getName(): string
    {
        return 'uppercase';
    }

    public function compile(string $expression): string
    {
        return '<?php $' . $expression . ' = strtoupper($' . $expression . '); ?>';
    }
}
```

### Cache Management

```php
use Lunar\Template\Cache\FilesystemCache;
use Lunar\Template\Cache\CacheWarmer;

// Create cache
$cache = new FilesystemCache('/path/to/cache');

// Check if cached and fresh
if ($cache->has('template-key', filemtime($templateFile))) {
    $compiled = $cache->get('template-key');
}

// Prewarm all templates
$warmer = new CacheWarmer('/path/to/templates', $cache);
$results = $warmer->warmRecursive();

foreach ($results as $template => $success) {
    echo $success ? "✓ $template" : "✗ $template";
}
```

## Security

- **XSS Protection**: All variables are automatically HTML-escaped by default
- **Path Validation**: Template paths are validated to prevent directory traversal attacks
- **Safe Compilation**: Compiled templates are stored only in designated cache directories
- **Circular Inheritance Detection**: Prevents infinite loops in template inheritance

## Performance

- **Smart Caching**: Templates are recompiled only when source changes
- **Cache Prewarming**: Precompile all templates during deployment
- **Optimized Compilation**: Efficient regex-based parsing
- **Memory Efficient**: Minimal memory footprint
- **OPcache Compatible**: Works great with PHP OPcache

## Framework Integration

### Laravel

```php
// In a Service Provider
$this->app->singleton(TemplateRenderer::class, function ($app) {
    $renderer = new TemplateRenderer(
        resource_path('templates'),
        storage_path('framework/cache/templates')
    );

    $renderer->registerMacro('route', fn($name) => route($name));

    return $renderer;
});
```

### Symfony

```yaml
# services.yaml
services:
    Lunar\Template\Renderer\TemplateRenderer:
        arguments:
            $templatePath: '%kernel.project_dir%/templates'
            $cachePath: '%kernel.cache_dir%/templates'
```

## Requirements

- **PHP 8.3+**
- **ext-mbstring** (for string handling)

## Contributing

Contributions are welcome! Please read our [Contributing Guidelines](CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Changelog

### v1.1.0
- Modular architecture (Parser, Compiler, Renderer)
- Multi-level template inheritance with `[% parent %]`
- `[% set %]` directive for variable assignment
- `[% include %]` directive for template inclusion
- `MacroRegistry` for centralized macro management
- `DateMacro` for date formatting
- `CacheInterface` and `FilesystemCache`
- `CacheWarmer` for template precompilation
- `InheritanceResolver` with circular detection
- 100% test coverage with PHPStan level 7

### v1.0.0
- Initial release
- Template inheritance system
- Block system
- Macro system
- Smart caching
- XSS protection
- Framework agnostic design

---

**Happy templating!**
