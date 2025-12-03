# Research: Lunar Template Engine

**Feature**: 001-template-engine
**Date**: 2025-12-03
**Status**: Complete

## Template Compilation Strategy

### Decision
Compile templates to native PHP files that can be cached and benefit from OPcache.

### Rationale
- PHP OPcache provides bytecode caching for compiled PHP files
- Native PHP execution is faster than interpreted template syntax
- File modification time comparison enables automatic cache invalidation
- No runtime parsing overhead after initial compilation

### Alternatives Considered
1. **Runtime interpretation**: Rejected - slower performance, no OPcache benefit
2. **AST caching with eval**: Rejected - security concerns, no OPcache benefit
3. **Serialize compiled structures**: Rejected - deserialization overhead, memory usage

## Template Syntax Design

### Decision
Use distinct delimiters for different purposes:
- `[[ variable ]]` - Variable output (auto-escaped)
- `[% directive %]` - Control structures (if, for, block, extends)
- `##macro(args)##` - Macro calls

### Rationale
- Clear visual distinction between output and logic
- Unlikely to conflict with HTML/JavaScript content
- Easy to parse with regex patterns
- Consistent with existing README documentation

### Alternatives Considered
1. **Twig-style `{{ }}` and `{% %}`**: Rejected - common in other engines, potential conflicts
2. **Mustache-style `{{ }}`**: Rejected - no built-in logic support
3. **PHP short tags**: Rejected - requires php.ini configuration, not portable

## Inheritance Resolution Strategy

### Decision
Use a bottom-up block collection approach:
1. Load child template
2. Recursively load parent templates
3. Collect block definitions from child to root
4. Render using most-specific block definitions

### Rationale
- Allows child templates to fully override parent blocks
- Supports multi-level inheritance chains
- Clear precedence rules (child wins)
- Circular inheritance detection via path tracking

### Alternatives Considered
1. **Top-down rendering**: Rejected - requires complex block placeholder management
2. **Include-based composition**: Rejected - less flexible than true inheritance

## Escaping Strategy

### Decision
Use PHP's `htmlspecialchars()` with `ENT_QUOTES | ENT_SUBSTITUTE` and UTF-8 encoding by default.

### Rationale
- Native PHP function, no dependencies
- Covers all XSS attack vectors for HTML context
- UTF-8 handles international characters
- `ENT_SUBSTITUTE` handles invalid sequences gracefully

### Alternatives Considered
1. **Custom escaping function**: Rejected - unnecessary complexity, potential bugs
2. **Context-aware escaping**: Deferred - HTML-only is sufficient for MVP, can extend later

## Cache Invalidation Strategy

### Decision
Use file modification time (mtime) comparison between source and cached files.

### Rationale
- Simple and reliable
- Works across all filesystems
- No external dependencies
- Automatic cache refresh when templates change

### Alternatives Considered
1. **Hash-based comparison**: Rejected - requires reading full file on every request
2. **Manual cache clearing only**: Rejected - poor developer experience
3. **Inotify/file watchers**: Rejected - adds complexity, platform-specific

## Error Handling Strategy

### Decision
Use a hierarchy of custom exception classes:
- `TemplateException` (base)
  - `TemplateNotFoundException`
  - `SyntaxException`
  - `CircularInheritanceException`
  - `MacroNotFoundException`

### Rationale
- Allows precise error catching by type
- Consistent with PHP best practices
- Enables informative error messages
- Supports debugging with line numbers where possible

### Alternatives Considered
1. **Single exception type with codes**: Rejected - less type-safe
2. **Error return values**: Rejected - not idiomatic PHP 8.x

## Macro System Design

### Decision
Interface-based macro system with two registration methods:
1. Closure-based: `registerMacro(string $name, callable $fn)`
2. Class-based: `registerMacroInstance(MacroInterface $macro)`

### Rationale
- Flexibility for simple and complex macros
- Interface enables IDE autocompletion
- Directory loading for organized macro collections
- Constructor injection support for class-based macros

### Alternatives Considered
1. **Closure-only**: Rejected - limits testability and organization
2. **Static methods**: Rejected - hard to test, no dependency injection

## Performance Benchmarks (Targets)

### Decision
Target performance metrics based on industry standards:
- First render: < 5ms for 10 variables
- Cached render: < 1ms
- Memory: < 10MB for 100KB templates
- Inheritance: 5 levels without degradation

### Rationale
- Competitive with established engines (Twig, Blade)
- Suitable for high-traffic applications
- Measurable and testable

### Data Sources
- Twig benchmarks: ~2-5ms first render
- Blade benchmarks: ~1-3ms first render
- PHP native include: ~0.1ms (baseline)
