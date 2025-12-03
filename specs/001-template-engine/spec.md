# Feature Specification: Lunar Template Engine

**Feature Branch**: `001-template-engine`
**Created**: 2025-12-03
**Status**: Draft
**Input**: User description: "Lunar Template Engine is a standalone, advanced template engine for PHP 8+ featuring template inheritance, blocks, macros, and intelligent caching. Performance, security."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Basic Template Rendering (Priority: P1)

As a PHP developer, I want to render templates with variable substitution so that I can generate dynamic HTML content without mixing PHP code with presentation logic.

**Why this priority**: This is the core functionality that every template engine must provide. Without basic rendering, no other features have value.

**Independent Test**: Can be fully tested by creating a simple template with variables, rendering it with data, and verifying the output HTML matches expectations.

**Acceptance Scenarios**:

1. **Given** a template file containing variable placeholders, **When** I render the template with an array of values, **Then** each placeholder is replaced with its corresponding value in the output.
2. **Given** a template with a variable that has no provided value, **When** I render the template, **Then** the placeholder is replaced with an empty string (no error thrown).
3. **Given** a template with nested object/array access syntax, **When** I render with nested data structures, **Then** the engine correctly resolves the nested values.

---

### User Story 2 - Template Inheritance (Priority: P2)

As a PHP developer, I want to extend base templates and override specific blocks so that I can maintain consistent layouts across my application while customizing individual pages.

**Why this priority**: Template inheritance is a key differentiator that enables DRY principles and maintainable template structures. It builds on basic rendering.

**Independent Test**: Can be tested by creating a parent template with blocks, a child template that extends it and overrides one block, then verifying the rendered output contains parent content with the overridden block.

**Acceptance Scenarios**:

1. **Given** a base template with defined blocks, **When** a child template extends it without overriding any blocks, **Then** the output contains all default block content from the parent.
2. **Given** a base template with a "content" block, **When** a child template extends it and defines its own "content" block, **Then** the child's block content replaces the parent's block content.
3. **Given** a multi-level inheritance chain (grandparent > parent > child), **When** rendering the child template, **Then** blocks are resolved correctly through the entire chain.

---

### User Story 3 - Macros for Reusable Components (Priority: P3)

As a PHP developer, I want to define and use macros (reusable template components) so that I can avoid duplicating common UI patterns across templates.

**Why this priority**: Macros provide component-like reusability, which is valuable for larger applications but not essential for basic templating.

**Independent Test**: Can be tested by registering a macro that generates HTML, using it in a template, and verifying the macro output appears correctly in the rendered result.

**Acceptance Scenarios**:

1. **Given** a registered macro that accepts parameters, **When** I call the macro in a template with arguments, **Then** the macro output is inserted with the provided arguments applied.
2. **Given** a macro that generates a link element, **When** I call it with URL and text parameters, **Then** the rendered output contains a properly formed anchor tag.
3. **Given** a directory of macro classes, **When** I load macros from that directory, **Then** all valid macro classes are registered and available for use.

---

### User Story 4 - Intelligent Caching (Priority: P4)

As a PHP developer, I want compiled templates to be cached automatically so that subsequent requests render faster without manual cache management.

**Why this priority**: Caching is critical for production performance but the engine must work correctly without it first.

**Independent Test**: Can be tested by rendering a template twice, measuring that the second render is faster, and verifying cache files are created in the designated directory.

**Acceptance Scenarios**:

1. **Given** a template that has never been rendered, **When** I render it, **Then** a compiled cache file is created in the cache directory.
2. **Given** a cached template, **When** I render it again without changes, **Then** the cached version is used (no recompilation).
3. **Given** a cached template whose source file has been modified, **When** I render it, **Then** the template is recompiled and the cache is updated.
4. **Given** a need to clear cache, **When** I call the cache clearing method, **Then** all cached templates (or a specific one) are removed.

---

### User Story 5 - Security: Auto-Escaping (Priority: P5)

As a PHP developer, I want all variable output to be automatically HTML-escaped so that my application is protected from XSS attacks by default.

**Why this priority**: Security is critical but is applied as a layer on top of the core rendering functionality.

**Independent Test**: Can be tested by rendering a template with potentially dangerous HTML/JavaScript in variables and verifying the output is safely escaped.

**Acceptance Scenarios**:

1. **Given** a variable containing `<script>alert('xss')</script>`, **When** I render a template using that variable, **Then** the output contains the escaped version `&lt;script&gt;...`.
2. **Given** a need to output raw HTML intentionally, **When** I use the raw/unescaped syntax, **Then** the HTML is output without escaping.
3. **Given** various special characters (`<`, `>`, `&`, `"`, `'`), **When** rendered through variables, **Then** all are properly escaped to their HTML entities.

---

### User Story 6 - Control Structures (Priority: P6)

As a PHP developer, I want to use conditionals and loops in my templates so that I can handle dynamic content and lists without embedding PHP code.

**Why this priority**: Control structures are essential for real-world templates but build on the core variable rendering.

**Independent Test**: Can be tested by creating templates with if/else conditions and for loops, rendering with appropriate data, and verifying correct conditional and iterative output.

**Acceptance Scenarios**:

1. **Given** a template with an if condition, **When** the condition evaluates to true, **Then** only the "if" block content is rendered.
2. **Given** a template with if/elseif/else blocks, **When** the first condition is false and second is true, **Then** only the "elseif" block content is rendered.
3. **Given** a template with a for loop over a collection, **When** rendered with an array of items, **Then** the loop body is repeated for each item with correct values.
4. **Given** an empty collection in a for loop, **When** rendered, **Then** the loop body is not rendered at all.

---

### Edge Cases

- What happens when a template file does not exist? (Should throw a clear, catchable exception)
- What happens when circular template inheritance is detected? (Should throw an exception to prevent infinite loops)
- What happens when a macro is called but not registered? (Should throw an exception with clear error message)
- What happens when cache directory is not writable? (Should throw an exception or fall back to uncached rendering)
- What happens when template syntax is malformed? (Should throw a parse exception with line number if possible)
- How does the system handle very large templates or deeply nested data? (Should handle gracefully within reasonable memory limits)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Engine MUST render templates by replacing variable placeholders with provided data values.
- **FR-002**: Engine MUST support dot notation for accessing nested object properties and array elements.
- **FR-003**: Engine MUST support template inheritance via an "extends" directive that allows child templates to extend parent templates.
- **FR-004**: Engine MUST support named blocks that can be defined in parent templates and overridden in child templates.
- **FR-005**: Engine MUST support conditional statements (if, elseif, else, endif) for conditional rendering.
- **FR-006**: Engine MUST support loop statements (for/foreach, endfor) for iterating over collections.
- **FR-007**: Engine MUST provide a macro system allowing registration of reusable callable components.
- **FR-008**: Engine MUST support loading macro classes from a specified directory.
- **FR-009**: Engine MUST automatically compile and cache templates for improved performance.
- **FR-010**: Engine MUST automatically detect template source changes and recompile when necessary.
- **FR-011**: Engine MUST provide methods to clear the template cache (all or specific templates).
- **FR-012**: Engine MUST automatically HTML-escape all variable output by default to prevent XSS.
- **FR-013**: Engine MUST provide syntax for outputting raw/unescaped content when explicitly needed.
- **FR-014**: Engine MUST validate and normalize template paths to prevent directory traversal attacks.
- **FR-015**: Engine MUST throw clear, catchable exceptions for error conditions (missing templates, syntax errors, etc.).
- **FR-016**: Engine MUST operate without any external runtime dependencies (standalone).
- **FR-017**: Engine MUST be compatible with PHP 8.3 or higher with strict typing enabled.

### Key Entities

- **Template**: A source file containing markup with placeholders, directives, and blocks. Has a path, content, and may extend another template.
- **Block**: A named section within a template that can be defined and overridden through inheritance. Has a name and content.
- **Macro**: A reusable callable component that accepts parameters and returns rendered output. Has a name and execution logic.
- **CompiledTemplate**: A cached, preprocessed version of a template optimized for repeated rendering. Associated with source template and modification timestamp.
- **RenderContext**: The collection of variables and state available during template rendering. Contains data values and registered macros.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Developers can render a basic template with 10 variables in under 5 milliseconds on first render.
- **SC-002**: Cached template renders complete in under 1 millisecond for subsequent requests.
- **SC-003**: Template inheritance chains of up to 5 levels deep render correctly without performance degradation.
- **SC-004**: All variable output is XSS-safe by default with zero configuration required.
- **SC-005**: Engine operates with zero external runtime dependencies (only PHP standard library).
- **SC-006**: 100% of edge cases (missing files, syntax errors, security attempts) produce clear, actionable error messages.
- **SC-007**: Developers can integrate the engine into any PHP 8.3+ project within 10 minutes using standard Composer installation.
- **SC-008**: Memory usage remains under 10MB for rendering templates up to 100KB in size.

## Assumptions

- **A-001**: Template syntax uses `[[ variable ]]` for output, `[% directive %]` for control structures, and `##macro()##` for macros (as shown in README).
- **A-002**: Caching uses the filesystem with compiled PHP files for optimal performance with OPcache.
- **A-003**: The engine is designed for server-side rendering of HTML but syntax is generic enough for other text formats.
- **A-004**: Error handling follows PHP exception best practices with custom exception classes for different error types.
- **A-005**: The engine targets web application use cases with typical template sizes (under 100KB source).
