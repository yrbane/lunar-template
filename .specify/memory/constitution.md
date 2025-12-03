<!--
SYNC IMPACT REPORT
==================
Version change: 1.0.0 → 1.1.0
Modified principles:
  - Principle I: Test-First - Added 100% code coverage requirement
Added sections: None
Removed sections: None
Templates requiring updates:
  - .specify/templates/plan-template.md ✅ (no updates needed - generic)
  - .specify/templates/spec-template.md ✅ (no updates needed - generic)
  - .specify/templates/tasks-template.md ✅ (no updates needed - generic)
Follow-up TODOs: None
==================
-->

# Lunar Template Engine Constitution

## Core Principles

### I. Test-First (NON-NEGOTIABLE)

Test-Driven Development is mandatory for all code changes in this project.

- All new features and bug fixes MUST follow the Red-Green-Refactor cycle:
  1. Write a failing test that defines the expected behavior
  2. Implement the minimum code to make the test pass
  3. Refactor while keeping tests green
- Tests MUST be written before implementation code
- No code MUST be merged without corresponding test coverage
- All `phpunit` tests MUST pass before any commit is created
- Tests MUST cover 100% of PHP code (full code coverage required)

**Rationale**: TDD ensures design quality, prevents regressions, and documents
expected behavior. Full code coverage guarantees no untested code paths exist.
This is the project's most critical principle.

### II. Zero Dependencies

Lunar Template Engine MUST remain a standalone library with no external runtime
dependencies.

- No external PHP frameworks or libraries MUST be required at runtime
- The library MUST be framework-agnostic and usable in any PHP project
- Development dependencies (PHPUnit, php-cs-fixer) are permitted
- Composer MUST only be used for autoloading and development tooling

**Rationale**: Zero dependencies ensure maximum portability, reduce security
attack surface, and eliminate version conflicts for consumers.

### III. Security by Default

All template output MUST be secure by default without requiring developer action.

- All variable output MUST be automatically HTML-escaped to prevent XSS
- Template paths MUST be validated and normalized to prevent directory traversal
- Compiled templates MUST be stored in designated cache directories only
- Raw/unescaped output MUST require explicit opt-in syntax

**Rationale**: Security vulnerabilities in template engines can affect all
applications using them. Safe defaults protect end users.

### IV. Performance & Caching

Smart caching and performance optimization MUST be prioritized throughout the
codebase.

- Templates MUST be compiled once and cached for subsequent requests
- Cache invalidation MUST occur automatically when source templates change
- The engine MUST be compatible with PHP OPcache
- Memory footprint MUST remain minimal during template rendering
- Regex patterns MUST be optimized and compiled once where possible

**Rationale**: Template engines are called frequently in web applications;
performance directly impacts application response times.

### V. Modern PHP Standards

All code MUST adhere to modern PHP standards and best practices.

- PHP 8.3+ MUST be the minimum supported version
- Strict typing MUST be enabled in all PHP files (`declare(strict_types=1)`)
- PSR-12 coding style MUST be followed and enforced via php-cs-fixer
- Named arguments and constructor property promotion SHOULD be used where
  appropriate
- Code style MUST be fixed via php-cs-fixer before any PHP commit

**Rationale**: Modern PHP features improve code safety, readability, and
maintainability while enabling better IDE support.

### VI. SOLID Design Principles

All code MUST follow SOLID principles and employ appropriate design patterns.

- **Single Responsibility**: Each class MUST have one reason to change
- **Open/Closed**: Classes MUST be open for extension, closed for modification
- **Liskov Substitution**: Derived classes MUST be substitutable for base classes
- **Interface Segregation**: Clients MUST NOT depend on interfaces they don't use
- **Dependency Inversion**: Depend on abstractions, not concretions
- Design patterns (Strategy, Factory, Template Method, etc.) MUST be applied
  where they simplify the solution

**Rationale**: SOLID principles and design patterns produce maintainable,
testable, and extensible code that scales with project complexity.

## Git Workflow & Commit Standards

All changes MUST follow strict git workflow and commit conventions.

### Branching Strategy

- Direct commits to `main` branch are FORBIDDEN
- All changes MUST go through Pull Requests
- Feature branches MUST be created for all work

### Commit Requirements

- Every commit MUST reference a GitHub issue (e.g., `#123` or `fixes #123`)
- All `phpunit` tests MUST pass before any commit is created
- Code style (php-cs-fixer) MUST be fixed before any PHP commit
- Commit messages MUST NOT contain references to AI assistants (e.g., no mention
  of Claude, GPT, Copilot, etc.)

### Commit Message Format

```
<type>: <description> (#<issue-number>)

[optional body]

[optional footer]
```

Where `<type>` is one of: `feat`, `fix`, `docs`, `style`, `refactor`, `test`,
`chore`.

**Rationale**: Strict workflow ensures code quality, traceability, and clean
git history while maintaining project integrity.

## Governance

This constitution defines the non-negotiable principles governing Lunar Template
Engine development. All contributors MUST comply with these principles.

### Amendment Procedure

1. Propose changes via GitHub issue with `constitution` label
2. Changes require maintainer approval
3. Document migration plan if changes affect existing code
4. Update version following semantic versioning rules

### Versioning Policy

- **MAJOR**: Backward-incompatible principle changes or removals
- **MINOR**: New principles or materially expanded guidance
- **PATCH**: Clarifications, wording, or non-semantic refinements

### Compliance Review

- All PRs MUST be reviewed for constitution compliance
- Constitution violations MUST be resolved before merge
- Complexity deviations MUST be documented and justified

**Version**: 1.1.0 | **Ratified**: 2025-12-03 | **Last Amended**: 2025-12-03
