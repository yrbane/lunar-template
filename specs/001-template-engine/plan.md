# Implementation Plan: Lunar Template Engine

**Branch**: `001-template-engine` | **Date**: 2025-12-03 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/001-template-engine/spec.md`

## Summary

Build a standalone, advanced template engine for PHP 8.3+ featuring template inheritance, blocks, macros, and intelligent caching. The engine prioritizes security (auto-escaping by default), performance (compiled template caching), and zero runtime dependencies.

## Technical Context

**Language/Version**: PHP 8.3+ with strict typing enabled
**Primary Dependencies**: None at runtime (PHPUnit, php-cs-fixer for development only)
**Storage**: Filesystem for template cache (compiled PHP files)
**Testing**: PHPUnit with 100% code coverage requirement
**Target Platform**: Any PHP 8.3+ environment (Linux, macOS, Windows)
**Project Type**: Single library package
**Performance Goals**: <5ms first render, <1ms cached render, <10MB memory for 100KB templates
**Constraints**: Zero runtime dependencies, PSR-12 compliant, OPcache compatible
**Scale/Scope**: Template files up to 100KB, inheritance chains up to 5 levels

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Test-First (NON-NEGOTIABLE) | PASS | All code will follow TDD with 100% coverage |
| II. Zero Dependencies | PASS | No runtime dependencies; only dev tools |
| III. Security by Default | PASS | Auto-escaping, path validation built-in |
| IV. Performance & Caching | PASS | Compiled template caching, OPcache support |
| V. Modern PHP Standards | PASS | PHP 8.3+, strict types, PSR-12 |
| VI. SOLID Design Principles | PASS | Interface-based design, single responsibility |
| Git Workflow | PASS | Feature branch, PRs required, issue references |

**Gate Status**: ALL GATES PASS - Proceeding to Phase 0

## Project Structure

### Documentation (this feature)

```text
specs/001-template-engine/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (API contracts)
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
src/
├── Compiler/
│   ├── CompilerInterface.php
│   ├── TemplateCompiler.php
│   └── Directive/
│       ├── DirectiveInterface.php
│       ├── BlockDirective.php
│       ├── ExtendsDirective.php
│       ├── IfDirective.php
│       └── ForDirective.php
├── Cache/
│   ├── CacheInterface.php
│   └── FilesystemCache.php
├── Macro/
│   ├── MacroInterface.php
│   ├── MacroRegistry.php
│   └── MacroLoader.php
├── Parser/
│   ├── ParserInterface.php
│   ├── TemplateParser.php
│   └── Token/
│       ├── TokenInterface.php
│       └── [Token types]
├── Renderer/
│   ├── RendererInterface.php
│   └── TemplateRenderer.php
├── Security/
│   ├── EscaperInterface.php
│   ├── HtmlEscaper.php
│   └── PathValidator.php
├── Exception/
│   ├── TemplateException.php
│   ├── TemplateNotFoundException.php
│   ├── SyntaxException.php
│   ├── CircularInheritanceException.php
│   └── MacroNotFoundException.php
└── AdvancedTemplateEngine.php

tests/
├── Unit/
│   ├── Compiler/
│   ├── Cache/
│   ├── Macro/
│   ├── Parser/
│   ├── Renderer/
│   └── Security/
├── Integration/
│   ├── InheritanceTest.php
│   ├── MacroTest.php
│   ├── CachingTest.php
│   └── SecurityTest.php
└── Fixtures/
    └── templates/
```

**Structure Decision**: Single library package structure following PSR-4 autoloading under `Lunar\Template` namespace. Separation of concerns via dedicated directories for each responsibility (Compiler, Cache, Parser, Renderer, etc.).

## Complexity Tracking

> No constitution violations - table not required.
