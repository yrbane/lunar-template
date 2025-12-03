# Quickstart: Lunar Template Engine

**Feature**: 001-template-engine
**Date**: 2025-12-03

## Installation

```bash
composer require lunar/template
```

## Basic Usage

### 1. Initialize the Engine

```php
<?php
declare(strict_types=1);

use Lunar\Template\AdvancedTemplateEngine;

$engine = new AdvancedTemplateEngine(
    templatePath: '/path/to/templates',
    cachePath: '/path/to/cache'
);
```

### 2. Create a Template

Create `templates/hello.tpl`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>[[ title ]]</title>
</head>
<body>
    <h1>Hello, [[ name ]]!</h1>
</body>
</html>
```

### 3. Render the Template

```php
$html = $engine->render('hello', [
    'title' => 'Welcome',
    'name' => 'World',
]);

echo $html;
```

**Output**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
    <h1>Hello, World!</h1>
</body>
</html>
```

## Template Inheritance

### Base Template (`templates/base.tpl`)

```html
<!DOCTYPE html>
<html>
<head>
    <title>[% block title %]Default Title[% endblock %]</title>
</head>
<body>
    <header>
        [% block header %]
        <h1>My Site</h1>
        [% endblock %]
    </header>

    <main>
        [% block content %]
        Default content
        [% endblock %]
    </main>

    <footer>
        [% block footer %]
        <p>&copy; 2024</p>
        [% endblock %]
    </footer>
</body>
</html>
```

### Child Template (`templates/page.tpl`)

```html
[% extends 'base.tpl' %]

[% block title %]My Page - My Site[% endblock %]

[% block content %]
<article>
    <h2>[[ article.title ]]</h2>
    <p>[[ article.content ]]</p>
</article>
[% endblock %]
```

### Render

```php
$html = $engine->render('page', [
    'article' => [
        'title' => 'Hello World',
        'content' => 'This is my first article.',
    ],
]);
```

## Control Structures

### Conditionals

```html
[% if user.isLoggedIn %]
    <p>Welcome back, [[ user.name ]]!</p>
[% elseif user.isGuest %]
    <p>Hello, guest!</p>
[% else %]
    <p>Please log in.</p>
[% endif %]
```

### Loops

```html
<ul>
[% for item in items %]
    <li>[[ item.name ]] - $[[ item.price ]]</li>
[% endfor %]
</ul>
```

## Macros

### Register a Closure Macro

```php
$engine->registerMacro('url', function(string $route, array $params = []): string {
    return '/route/' . $route . '?' . http_build_query($params);
});
```

### Use in Template

```html
<a href="##url('products', ['id' => product.id])##">View Product</a>
```

### Class-Based Macro

```php
<?php
declare(strict_types=1);

namespace App\Template\Macro;

use Lunar\Template\Macro\MacroInterface;

class AssetMacro implements MacroInterface
{
    public function __construct(
        private string $basePath = '/assets'
    ) {}

    public function getName(): string
    {
        return 'asset';
    }

    public function execute(array $args): string
    {
        $path = $args[0] ?? '';
        return $this->basePath . '/' . ltrim($path, '/');
    }
}

// Register
$engine->registerMacroInstance(new AssetMacro('/static'));
```

### Auto-Load Macros

```php
$engine->loadMacrosFromDirectory(
    'App\\Template\\Macro',
    __DIR__ . '/src/Template/Macro'
);
```

## Security

### Auto-Escaping (Default)

All `[[ variable ]]` output is automatically HTML-escaped:

```php
$engine->render('template', [
    'userInput' => '<script>alert("xss")</script>',
]);
// Output: &lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;
```

### Raw Output (Opt-in)

Use `[[! variable !]]` for trusted HTML:

```html
[[! trustedHtml !]]
```

## Cache Management

### Clear All Cache

```php
$engine->clearCache();
```

### Clear Specific Template

```php
$engine->clearCache('page');
```

## Error Handling

```php
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Exception\TemplateNotFoundException;
use Lunar\Template\Exception\SyntaxException;

try {
    $html = $engine->render('missing-template', []);
} catch (TemplateNotFoundException $e) {
    // Handle missing template
    echo "Template not found: " . $e->getTemplatePath();
} catch (SyntaxException $e) {
    // Handle syntax error
    echo "Syntax error at line " . $e->getLine();
} catch (TemplateException $e) {
    // Handle any template error
    echo "Template error: " . $e->getMessage();
}
```

## Validation Checklist

After implementation, verify:

- [ ] Basic variable rendering works
- [ ] Nested object/array access works
- [ ] Template inheritance renders correctly
- [ ] Blocks override parent content
- [ ] Conditionals evaluate correctly
- [ ] Loops iterate properly
- [ ] Macros execute and return output
- [ ] Auto-escaping prevents XSS
- [ ] Raw output works when needed
- [ ] Cache is created on first render
- [ ] Cache is used on subsequent renders
- [ ] Cache invalidates on source change
- [ ] Missing templates throw TemplateNotFoundException
- [ ] Syntax errors throw SyntaxException with line number
- [ ] Circular inheritance throws CircularInheritanceException
