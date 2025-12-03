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
composer require yrbane/lunar-template
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

### Raw Output (No Escaping)

Use `[[! ... !]]` syntax to output content without HTML escaping:

```html
<!-- Raw output - NO escaping (use with trusted content only!) -->
[[! htmlContent !]]

<!-- With filters -->
[[! trustedHtml | trim !]]

<!-- For user-generated content, prefer the raw filter instead -->
[[ content | raw ]]
```

> **Warning**: Only use `[[! !]]` with trusted content. For user input, always use regular `[[ ]]` syntax with automatic escaping.

### Filters

Filters transform variable values using the pipe syntax:

```html
<!-- Single filter -->
<h1>[[ title | upper ]]</h1>

<!-- Chained filters -->
<p>[[ text | trim | lower | slug ]]</p>

<!-- Filter with arguments -->
<p>[[ price | number_format(2) ]]</p>
<p>[[ description | truncate(100, "...") ]]</p>

<!-- Raw output (no escaping) -->
<div>[[ htmlContent | raw ]]</div>
```

#### Built-in Filters

**String Filters:**
- `upper` - Uppercase
- `lower` - Lowercase
- `capitalize` - Capitalize first letter
- `title` - Title Case
- `trim`, `ltrim`, `rtrim` - Trim whitespace
- `slug` - URL-friendly slug
- `truncate(length, suffix)` - Truncate text
- `excerpt(length)` - Smart excerpt
- `wordwrap(width)` - Word wrap
- `reverse` - Reverse string
- `repeat(times)` - Repeat string
- `pad_left(length, char)`, `pad_right(length, char)` - Pad string
- `replace(search, replace)` - Replace text
- `split(delimiter)` - Split into array

**Number Filters:**
- `number_format(decimals, dec_point, thousands_sep)` - Format number
- `round(precision)` - Round number
- `floor`, `ceil` - Round down/up
- `abs` - Absolute value
- `currency(symbol)` - Format as currency
- `percent(decimals)` - Format as percentage
- `ordinal` - Ordinal suffix (1st, 2nd, 3rd)
- `filesize(decimals)` - Human-readable file size

**Array Filters:**
- `first`, `last` - First/last element
- `length` - Array/string length
- `keys`, `values` - Array keys/values
- `sort(key, direction)` - Sort array
- `slice(start, length)` - Slice array
- `merge(array)` - Merge arrays
- `unique` - Remove duplicates
- `join(glue, lastGlue)` - Join to string
- `chunk(size)` - Split into chunks
- `pluck(key)` - Extract values by key
- `filter(key, value)` - Filter array
- `map(filter)` - Apply filter to each element
- `group_by(key)` - Group by key
- `random` - Random element
- `shuffle` - Shuffle array

**Date Filters:**
- `date(format)` - Format date
- `ago` - Relative time (e.g., "5 minutes ago")
- `relative` - Relative date (e.g., "yesterday")

**Encoding Filters:**
- `base64_encode`, `base64_decode` - Base64
- `url_encode`, `url_decode` - URL encoding
- `json_encode(pretty)`, `json_decode` - JSON
- `md5`, `sha1`, `sha256` - Hashing

**HTML Filters:**
- `raw` - No escaping (use with caution!)
- `escape(strategy)` - Escape (html, js, css, url)
- `striptags(allowed)` - Strip HTML tags
- `nl2br` - Newlines to `<br>`
- `spaceless` - Remove whitespace between tags

**HTML Formatting Filters:**
- `markdown` - Convert Markdown to HTML (bold, italic, links, headers, lists, code)
- `linkify(target)` - Auto-convert URLs and emails to clickable links
- `list(type, class)` - Convert array to HTML list (`<ul>` or `<ol>`)
- `table(hasHeader, class)` - Convert 2D array to HTML table
- `attributes` - Convert array to HTML attributes string
- `wrap(tag, class, id)` - Wrap content in HTML tag
- `highlight(term, class)` - Highlight search terms with `<mark>`
- `paragraph`, `p` - Convert text blocks to `<p>` paragraphs
- `heading`, `h` - Create HTML heading (h1-h6): `[[ title | h(2) ]]`
- `anchor`, `a` - Create anchor/link: `[[ url | a("Click here") ]]`
- `excerpt_html(length, suffix)` - Strip HTML and create excerpt
- `class_list` - Build CSS class string from array

**HTML Element Filters:**
- `div(class, id)` - Wrap in `<div>`: `[[ content | div("container") ]]`
- `span(class, id)` - Wrap in `<span>`: `[[ text | span("highlight") ]]`
- `strong(class)` - Wrap in `<strong>`: `[[ text | strong ]]`
- `em(class)` - Wrap in `<em>` (italic): `[[ text | em ]]`
- `small(class)` - Wrap in `<small>`: `[[ text | small ]]`
- `code(language)` - Wrap in `<code>`: `[[ code | code("php") ]]`
- `pre(class)` - Wrap in `<pre>`: `[[ text | pre ]]`
- `blockquote(cite, class)` - Create blockquote with optional citation
- `abbr(title)` - Create abbreviation: `[[ "HTML" | abbr("HyperText Markup Language") ]]`
- `time(format, class)` - Create `<time>` element with datetime attribute
- `img(alt, class, lazy)` - Create `<img>`: `[[ url | img("Alt text", "photo", true) ]]`
- `video(autoplay, loop, muted, class)` - Create `<video>` with controls
- `audio(autoplay, loop, class)` - Create `<audio>` with controls
- `iframe(title, class, lazy)` - Create `<iframe>` with allowfullscreen
- `progress(max, class)` - Create `<progress>` bar: `[[ 75 | progress ]]`
- `meter(min, max, low, high)` - Create `<meter>` gauge
- `badge(variant)` - Create badge: `[[ "New" | badge("success") ]]`
- `button(type, class, disabled)` - Create `<button>`: `[[ "Submit" | button("submit", "btn") ]]`

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

#### Core Macros
```html
<!-- Asset URLs -->
<link rel="stylesheet" href="##asset('/css/app.css')##">

<!-- Date formatting -->
<time>##date('F j, Y')##</time>
<time>##date('Y-m-d', article.publishedAt)##</time>
```

#### Utility Macros
```html
<!-- UUID generation -->
##uuid()##                              <!-- e.g., 550e8400-e29b-41d4-a716-446655440000 -->

<!-- Random values -->
##random()##                            <!-- Random 0-100 -->
##random(1, 10)##                       <!-- Random 1-10 -->
##random("string", 16)##                <!-- Random hex string -->
##random("alpha", 8)##                  <!-- Random letters -->
##random("alnum", 12)##                 <!-- Random alphanumeric -->
##random("token", 32)##                 <!-- URL-safe token -->

<!-- Lorem ipsum -->
##lorem()##                             <!-- 1 paragraph -->
##lorem(3)##                            <!-- 3 paragraphs -->
##lorem("words", 10)##                  <!-- 10 words -->
##lorem("sentences", 5)##               <!-- 5 sentences -->

<!-- Current time -->
##now()##                               <!-- Unix timestamp -->
##now("Y-m-d")##                        <!-- Formatted date -->
##now("iso")##                          <!-- ISO 8601 -->

<!-- Debug dump -->
##dump(variable)##                      <!-- Styled debug output -->

<!-- JSON encoding -->
##json(data)##                          <!-- Compact JSON -->
##json(data, true)##                    <!-- Pretty JSON -->

<!-- Pluralization -->
##pluralize(5, "item", "items")##       <!-- "5 items" -->
##pluralize(1, "child", "children")##   <!-- "1 child" -->

<!-- Money formatting -->
##money(99.99, "USD")##                 <!-- $99.99 -->
##money(50, "EUR")##                    <!-- €50,00 -->

<!-- Data masking -->
##mask("john@example.com", "email")##   <!-- j***@example.com -->
##mask("4111111111111111", "card")##    <!-- **** **** **** 1111 -->
##mask("555-123-4567", "phone")##       <!-- ***-***-4567 -->

<!-- Initials extraction -->
##initials("John Doe")##                <!-- JD -->
##initials("John William Doe", 3)##     <!-- JWD -->

<!-- Color manipulation -->
##color("primary")##                    <!-- #4F46E5 (from palette) -->
##color("random")##                     <!-- Random hex color -->
##color("#FF0000", "lighten", 20)##     <!-- Lighten by 20% -->
##color("#FF0000", "darken", 20)##      <!-- Darken by 20% -->
##color("#FF0000", "alpha", 0.5)##      <!-- rgba(255, 0, 0, 0.5) -->
##color("#FF0000", "rgb")##             <!-- rgb(255, 0, 0) -->
##color("#FF0000", "hsl")##             <!-- hsl(0, 100%, 50%) -->

<!-- Relative time -->
##timeago(timestamp)##                  <!-- "5 minutes ago" -->
##timeago(date, "short")##              <!-- "5m ago" -->

<!-- Countdown -->
##countdown("2025-12-31")##             <!-- "28 days, 5 hours" -->
##countdown("2025-12-31", "full")##     <!-- "28d 5h 30m 15s" -->
##countdown("2025-12-31", "days")##     <!-- "28 days" -->
```

#### Security Macros
```html
<!-- CSRF protection -->
##csrf()##                              <!-- Hidden input field -->
##csrf("token")##                       <!-- Token value only -->
##csrf("meta")##                        <!-- Meta tag -->

<!-- CSP nonce -->
##nonce()##                             <!-- Nonce value -->
##nonce("script")##                     <!-- nonce="..." attribute -->

<!-- Honeypot spam protection -->
##honeypot()##                          <!-- Hidden honeypot field -->
##honeypot("website")##                 <!-- Custom field name -->
```

#### Form Macros
```html
<!-- Input elements -->
##input("email", "email")##
##input("username", "text", "John")##
##input("age", "number", "", "min=0 max=120")##

<!-- Textarea -->
##textarea("message")##
##textarea("bio", "Default text", 5, 40)##

<!-- Select dropdown -->
##select("country", countries)##
##select("country", countries, "FR", "Choose...")##

<!-- Checkboxes & Radio -->
##checkbox("agree", "1", false, "I agree")##
##radio("gender", "male", false, "Male")##

<!-- Other form elements -->
##label("email", "Email Address")##
##hidden("user_id", "123")##
##method("PUT")##                       <!-- Method spoofing -->
```

#### HTML/Meta Macros
```html
<!-- Script & Style -->
##script("/js/app.js")##
##script("/js/app.js", "defer")##
##style("/css/app.css")##
##style("/css/app.css", "preload")##

<!-- Meta tags -->
##meta("description", "Page description")##
##meta("robots", "noindex")##

<!-- Open Graph -->
##og("title", "Page Title")##
##og("image", "https://example.com/img.jpg")##

<!-- Twitter Cards -->
##twitter("card", "summary_large_image")##
##twitter("title", "Page Title")##

<!-- SEO -->
##canonical("https://example.com/page")##
##favicon("/favicon.ico")##
##favicon("/favicon.ico", "full")##     <!-- Full favicon set -->

<!-- Schema.org JSON-LD -->
##schema("Organization", data)##
##schema("Article", articleData)##

<!-- Breadcrumbs -->
##breadcrumbs(items)##                  <!-- With Schema.org markup -->

<!-- Icons (multiple libraries) -->
##icon("user")##                        <!-- Heroicons outline -->
##icon("home", "solid")##               <!-- Heroicons solid -->
##icon("fa-home", "fa")##               <!-- Font Awesome -->
##icon("mdi-account", "mdi")##          <!-- Material Design -->
##icon("bi-person", "bi")##             <!-- Bootstrap Icons -->
##icon("user", "lucide")##              <!-- Lucide -->
```

#### Image/Media Macros
```html
<!-- Gravatar -->
##gravatar("email@example.com")##
##gravatar("email@example.com", 200)##
##gravatar("email@example.com", 100, "identicon")##

<!-- UI Avatars -->
##avatar("John Doe")##
##avatar("John Doe", 100, "4F46E5", "FFFFFF")##

<!-- Placeholder images -->
##placeholder(800, 600)##
##placeholder(400, 300, "Product Image")##

<!-- QR Codes -->
##qrcode("https://example.com")##
##qrcode("https://example.com", 200)##
```

#### Embed Macros
```html
<!-- YouTube -->
##youtube("dQw4w9WgXcQ")##
##youtube("dQw4w9WgXcQ", 560, 315)##
##youtube("dQw4w9WgXcQ", 560, 315, true)##  <!-- Autoplay -->

<!-- Vimeo -->
##vimeo("123456789")##
##vimeo("123456789", 640, 360)##
```

#### Social Share Macros
```html
<!-- Social sharing URLs -->
##share("twitter", "https://example.com", "Check this out!")##
##share("facebook", "https://example.com")##
##share("linkedin", "https://example.com", "Title")##
##share("email", "https://example.com", "Subject", "Body")##
##share("whatsapp", "https://example.com", "Message")##
##share("telegram", "https://example.com")##
##share("reddit", "https://example.com", "Title")##
##share("pinterest", "https://example.com", "Description", "image_url")##
```

## Architecture

### Components

| Component | Description |
|-----------|-------------|
| `TemplateParser` | Parses template source into structured components |
| `TemplateCompiler` | Compiles template syntax to PHP code |
| `TemplateRenderer` | Renders templates with variable injection |
| `InheritanceResolver` | Handles multi-level template inheritance |
| `FilterRegistry` | Centralized filter management with 50+ built-in filters |
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

### Custom Filters

```php
<?php
use Lunar\Template\Filter\FilterInterface;

class HighlightFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'highlight';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $term = $args[0] ?? '';
        return str_replace($term, "<mark>$term</mark>", (string) $value);
    }
}

// Register with renderer
$renderer->registerFilterInstance(new HighlightFilter());

// Or register a simple callable
$renderer->registerFilter('double', fn($value) => $value * 2);
```

**Use in template:**
```html
<p>[[ text | highlight("search term") ]]</p>
<p>Total: [[ count | double ]]</p>
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

### v1.4.0
- **Raw Output Syntax**: `[[! content !]]` for unescaped output
- **40+ New Macros**:
  - Utility: `uuid`, `random`, `lorem`, `now`, `dump`, `json`, `pluralize`, `money`, `mask`, `initials`, `color`, `timeago`, `countdown`
  - Security: `csrf`, `nonce`, `honeypot`
  - Form: `input`, `textarea`, `select`, `checkbox`, `radio`, `label`, `hidden`, `method`
  - HTML/Meta: `script`, `style`, `meta`, `og`, `twitter`, `canonical`, `favicon`, `schema`, `breadcrumbs`, `icon`
  - Image/Media: `gravatar`, `avatar`, `placeholder`, `qrcode`
  - Embed: `youtube`, `vimeo`
  - Social: `share` (twitter, facebook, linkedin, email, whatsapp, telegram, reddit, pinterest)
- **DefaultMacros**: Register all built-in macros at once

### v1.3.0
- **HTML Formatting Filters**: 12 filters for HTML generation
- `markdown`, `linkify`, `list`, `table`, `attributes`, `wrap`
- `highlight`, `paragraph`, `heading`, `anchor`, `excerpt_html`, `class_list`
- **HTML Element Filters**: 18 new filters for HTML elements
- Text: `div`, `span`, `strong`, `em`, `small`, `code`, `pre`, `blockquote`, `abbr`
- Media: `img`, `video`, `audio`, `iframe`
- UI: `time`, `progress`, `meter`, `badge`, `button`
- **Aliases**: `p` (paragraph), `h` (heading), `a` (anchor)

### v1.2.0
- **Filter System**: 50+ built-in filters with pipe syntax `[[ var | filter ]]`
- String filters: `upper`, `lower`, `capitalize`, `title`, `trim`, `slug`, `truncate`, `excerpt`, `replace`, `split`
- Number filters: `number_format`, `round`, `floor`, `ceil`, `abs`, `currency`, `percent`, `ordinal`, `filesize`
- Array filters: `first`, `last`, `length`, `keys`, `values`, `sort`, `slice`, `merge`, `unique`, `join`, `chunk`, `pluck`, `filter`, `map`, `group_by`, `random`, `shuffle`
- Date filters: `date`, `ago`, `relative`
- Encoding filters: `base64_encode`, `base64_decode`, `url_encode`, `url_decode`, `json_encode`, `json_decode`, `md5`, `sha1`, `sha256`
- HTML filters: `raw`, `escape`, `striptags`, `nl2br`, `spaceless`
- Filter chaining support: `[[ text | trim | upper | slug ]]`
- Custom filter registration via `FilterInterface`
- `FilterRegistry` for centralized filter management

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
