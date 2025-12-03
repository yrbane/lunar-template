# Data Model: Lunar Template Engine

**Feature**: 001-template-engine
**Date**: 2025-12-03

## Core Entities

### Template

Represents a source template file.

| Attribute | Type | Description |
|-----------|------|-------------|
| path | string | Relative path from template root |
| absolutePath | string | Full filesystem path |
| content | string | Raw template source content |
| parent | ?Template | Parent template if extends directive present |
| blocks | Block[] | Named blocks defined in this template |
| modifiedAt | int | File modification timestamp |

**Validation Rules**:
- Path must not contain `..` or absolute paths (security)
- Path must resolve to a file within template root
- Content must be valid UTF-8

**State Transitions**: N/A (immutable after load)

### Block

Represents a named content section within a template.

| Attribute | Type | Description |
|-----------|------|-------------|
| name | string | Unique block identifier |
| content | string | Block content (may contain nested directives) |
| template | Template | Owning template reference |

**Validation Rules**:
- Name must be alphanumeric with underscores
- Name must be unique within a template

### CompiledTemplate

Cached, compiled version of a template.

| Attribute | Type | Description |
|-----------|------|-------------|
| sourcePath | string | Original template path |
| cachePath | string | Path to compiled PHP file |
| sourceModifiedAt | int | Source file mtime at compilation |
| compiledAt | int | Compilation timestamp |

**Validation Rules**:
- Cache path must be within designated cache directory
- Compiled file must be valid PHP

**State Transitions**:
- `Fresh` → `Stale` (when source mtime > sourceModifiedAt)
- `Stale` → `Fresh` (after recompilation)

### Macro

Reusable callable component.

| Attribute | Type | Description |
|-----------|------|-------------|
| name | string | Unique macro identifier |
| callable | callable | Function/closure to execute |
| instance | ?MacroInterface | Class instance (if class-based) |

**Validation Rules**:
- Name must be alphanumeric with underscores
- Callable must be valid PHP callable

### RenderContext

Runtime state during template rendering.

| Attribute | Type | Description |
|-----------|------|-------------|
| variables | array | Key-value pairs of template data |
| macros | MacroRegistry | Registered macros |
| currentTemplate | Template | Template being rendered |
| blockStack | Block[] | Block resolution stack |
| inheritanceChain | string[] | Paths for circular detection |

## Relationships

```
Template 1──n Block
    │
    │ extends (0..1)
    ▼
Template (parent)

CompiledTemplate 1──1 Template (source)

RenderContext 1──n Macro
              1──1 Template (current)
              1──n Block (stack)
```

## Interfaces

### TemplateEngineInterface

Main facade for template operations.

```
render(string $template, array $data): string
templateExists(string $template): bool
clearCache(?string $template = null): void
registerMacro(string $name, callable $fn): void
registerMacroInstance(MacroInterface $macro): void
loadMacrosFromDirectory(string $namespace, string $path): void
getRegisteredMacros(): array
```

### CompilerInterface

Template compilation contract.

```
compile(Template $template): string
compileString(string $content): string
```

### CacheInterface

Cache operations contract.

```
get(string $key): ?CompiledTemplate
set(string $key, CompiledTemplate $compiled): void
has(string $key): bool
delete(string $key): void
clear(): void
isFresh(string $key, int $sourceModifiedAt): bool
```

### MacroInterface

Contract for class-based macros.

```
getName(): string
execute(array $args): string
```

### EscaperInterface

Output escaping contract.

```
escape(mixed $value): string
```

### ParserInterface

Template parsing contract.

```
parse(string $content): array  // Returns token array
```

### RendererInterface

Template rendering contract.

```
render(Template $template, RenderContext $context): string
```

## Exception Hierarchy

```
TemplateException (base)
├── TemplateNotFoundException
│   └── message: "Template not found: {path}"
├── SyntaxException
│   └── message: "Syntax error at line {line}: {details}"
├── CircularInheritanceException
│   └── message: "Circular inheritance: {chain}"
└── MacroNotFoundException
    └── message: "Macro not found: {name}"
```
