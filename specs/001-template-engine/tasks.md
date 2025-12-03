# Tasks: Lunar Template Engine

**Input**: Design documents from `/specs/001-template-engine/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Required per constitution (Test-First principle with 100% code coverage)

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Single project**: `src/`, `tests/` at repository root
- Namespace: `Lunar\Template`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [ ] T001 Create project directory structure per plan.md in src/
- [ ] T002 Initialize Composer project with PSR-4 autoloading for Lunar\Template namespace in composer.json
- [ ] T003 [P] Configure PHPUnit with coverage reporting in phpunit.xml
- [ ] T004 [P] Configure php-cs-fixer with PSR-12 rules in .php-cs-fixer.php
- [ ] T005 Create test fixtures directory structure in tests/Fixtures/templates/

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

### Tests for Foundational

- [ ] T006 [P] Unit test for TemplateException in tests/Unit/Exception/TemplateExceptionTest.php
- [ ] T007 [P] Unit test for TemplateNotFoundException in tests/Unit/Exception/TemplateNotFoundExceptionTest.php
- [ ] T008 [P] Unit test for SyntaxException in tests/Unit/Exception/SyntaxExceptionTest.php
- [ ] T009 [P] Unit test for PathValidator in tests/Unit/Security/PathValidatorTest.php

### Implementation for Foundational

- [ ] T010 [P] Create TemplateException base class in src/Exception/TemplateException.php
- [ ] T011 [P] Create TemplateNotFoundException in src/Exception/TemplateNotFoundException.php
- [ ] T012 [P] Create SyntaxException with line number support in src/Exception/SyntaxException.php
- [ ] T013 [P] Create CircularInheritanceException in src/Exception/CircularInheritanceException.php
- [ ] T014 [P] Create MacroNotFoundException in src/Exception/MacroNotFoundException.php
- [ ] T015 Create PathValidator with directory traversal protection in src/Security/PathValidator.php

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 1 - Basic Template Rendering (Priority: P1) 🎯 MVP

**Goal**: Render templates with variable substitution and dot notation access

**Independent Test**: Create a template with `[[ title ]]` and `[[ author.name ]]`, render with data, verify output

### Tests for User Story 1

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [ ] T016 [P] [US1] Unit test for HtmlEscaper in tests/Unit/Security/HtmlEscaperTest.php
- [ ] T017 [P] [US1] Unit test for TemplateParser variable parsing in tests/Unit/Parser/TemplateParserTest.php
- [ ] T018 [P] [US1] Unit test for TemplateRenderer in tests/Unit/Renderer/TemplateRendererTest.php
- [ ] T019 [US1] Integration test for basic rendering in tests/Integration/BasicRenderingTest.php

### Implementation for User Story 1

- [ ] T020 [P] [US1] Create EscaperInterface in src/Security/EscaperInterface.php
- [ ] T021 [P] [US1] Create HtmlEscaper with htmlspecialchars in src/Security/HtmlEscaper.php
- [ ] T022 [P] [US1] Create ParserInterface in src/Parser/ParserInterface.php
- [ ] T023 [US1] Create TemplateParser for variable extraction in src/Parser/TemplateParser.php
- [ ] T024 [P] [US1] Create RendererInterface in src/Renderer/RendererInterface.php
- [ ] T025 [US1] Create TemplateRenderer with dot notation support in src/Renderer/TemplateRenderer.php
- [ ] T026 [US1] Create AdvancedTemplateEngine facade (render, templateExists) in src/AdvancedTemplateEngine.php
- [ ] T027 [US1] Create test fixtures for basic templates in tests/Fixtures/templates/basic/

**Checkpoint**: User Story 1 complete - basic template rendering works independently

---

## Phase 4: User Story 2 - Template Inheritance (Priority: P2)

**Goal**: Extend base templates and override specific blocks

**Independent Test**: Create parent.tpl with blocks, child.tpl that extends and overrides, verify merged output

### Tests for User Story 2

- [ ] T028 [P] [US2] Unit test for BlockDirective in tests/Unit/Compiler/Directive/BlockDirectiveTest.php
- [ ] T029 [P] [US2] Unit test for ExtendsDirective in tests/Unit/Compiler/Directive/ExtendsDirectiveTest.php
- [ ] T030 [US2] Integration test for inheritance in tests/Integration/InheritanceTest.php

### Implementation for User Story 2

- [ ] T031 [P] [US2] Create DirectiveInterface in src/Compiler/Directive/DirectiveInterface.php
- [ ] T032 [P] [US2] Create CompilerInterface in src/Compiler/CompilerInterface.php
- [ ] T033 [US2] Create BlockDirective for block parsing in src/Compiler/Directive/BlockDirective.php
- [ ] T034 [US2] Create ExtendsDirective for inheritance in src/Compiler/Directive/ExtendsDirective.php
- [ ] T035 [US2] Create TemplateCompiler with inheritance resolution in src/Compiler/TemplateCompiler.php
- [ ] T036 [US2] Add circular inheritance detection to TemplateCompiler in src/Compiler/TemplateCompiler.php
- [ ] T037 [US2] Update AdvancedTemplateEngine for inheritance support in src/AdvancedTemplateEngine.php
- [ ] T038 [US2] Create test fixtures for inheritance in tests/Fixtures/templates/inheritance/

**Checkpoint**: User Story 2 complete - template inheritance works independently

---

## Phase 5: User Story 3 - Macros (Priority: P3)

**Goal**: Define and use reusable macro components

**Independent Test**: Register a macro, use `##macroName(arg)##` in template, verify output

### Tests for User Story 3

- [ ] T039 [P] [US3] Unit test for MacroInterface in tests/Unit/Macro/MacroInterfaceTest.php
- [ ] T040 [P] [US3] Unit test for MacroRegistry in tests/Unit/Macro/MacroRegistryTest.php
- [ ] T041 [P] [US3] Unit test for MacroLoader in tests/Unit/Macro/MacroLoaderTest.php
- [ ] T042 [US3] Integration test for macros in tests/Integration/MacroTest.php

### Implementation for User Story 3

- [ ] T043 [P] [US3] Create MacroInterface in src/Macro/MacroInterface.php
- [ ] T044 [US3] Create MacroRegistry for macro storage in src/Macro/MacroRegistry.php
- [ ] T045 [US3] Create MacroLoader for directory loading in src/Macro/MacroLoader.php
- [ ] T046 [US3] Add macro parsing to TemplateParser in src/Parser/TemplateParser.php
- [ ] T047 [US3] Add macro execution to TemplateRenderer in src/Renderer/TemplateRenderer.php
- [ ] T048 [US3] Add registerMacro, registerMacroInstance, loadMacrosFromDirectory to AdvancedTemplateEngine in src/AdvancedTemplateEngine.php
- [ ] T049 [US3] Create test fixtures for macros in tests/Fixtures/templates/macros/

**Checkpoint**: User Story 3 complete - macros work independently

---

## Phase 6: User Story 4 - Intelligent Caching (Priority: P4)

**Goal**: Automatically compile and cache templates for performance

**Independent Test**: Render template twice, verify cache file created, second render uses cache

### Tests for User Story 4

- [ ] T050 [P] [US4] Unit test for CacheInterface in tests/Unit/Cache/CacheInterfaceTest.php
- [ ] T051 [P] [US4] Unit test for FilesystemCache in tests/Unit/Cache/FilesystemCacheTest.php
- [ ] T052 [US4] Integration test for caching in tests/Integration/CachingTest.php

### Implementation for User Story 4

- [ ] T053 [P] [US4] Create CacheInterface in src/Cache/CacheInterface.php
- [ ] T054 [US4] Create FilesystemCache with mtime invalidation in src/Cache/FilesystemCache.php
- [ ] T055 [US4] Integrate caching into TemplateCompiler in src/Compiler/TemplateCompiler.php
- [ ] T056 [US4] Add clearCache method to AdvancedTemplateEngine in src/AdvancedTemplateEngine.php
- [ ] T057 [US4] Create test fixtures for caching in tests/Fixtures/templates/caching/

**Checkpoint**: User Story 4 complete - caching works independently

---

## Phase 7: User Story 5 - Security: Auto-Escaping (Priority: P5)

**Goal**: All variable output HTML-escaped by default, raw syntax for opt-out

**Independent Test**: Render `<script>` in variable, verify escaped; use raw syntax, verify unescaped

### Tests for User Story 5

- [ ] T058 [P] [US5] Unit test for raw output syntax in tests/Unit/Parser/RawOutputTest.php
- [ ] T059 [US5] Integration test for XSS prevention in tests/Integration/SecurityTest.php

### Implementation for User Story 5

- [ ] T060 [US5] Add raw output syntax `[[! var !]]` parsing to TemplateParser in src/Parser/TemplateParser.php
- [ ] T061 [US5] Add raw output rendering to TemplateRenderer in src/Renderer/TemplateRenderer.php
- [ ] T062 [US5] Create test fixtures with XSS payloads in tests/Fixtures/templates/security/

**Checkpoint**: User Story 5 complete - security features work independently

---

## Phase 8: User Story 6 - Control Structures (Priority: P6)

**Goal**: Conditionals (if/elseif/else) and loops (for) in templates

**Independent Test**: Create template with `[% if %]` and `[% for %]`, render with data, verify output

### Tests for User Story 6

- [ ] T063 [P] [US6] Unit test for IfDirective in tests/Unit/Compiler/Directive/IfDirectiveTest.php
- [ ] T064 [P] [US6] Unit test for ForDirective in tests/Unit/Compiler/Directive/ForDirectiveTest.php
- [ ] T065 [US6] Integration test for control structures in tests/Integration/ControlStructuresTest.php

### Implementation for User Story 6

- [ ] T066 [US6] Create IfDirective for conditionals in src/Compiler/Directive/IfDirective.php
- [ ] T067 [US6] Create ForDirective for loops in src/Compiler/Directive/ForDirective.php
- [ ] T068 [US6] Register directives in TemplateCompiler in src/Compiler/TemplateCompiler.php
- [ ] T069 [US6] Create test fixtures for control structures in tests/Fixtures/templates/control/

**Checkpoint**: User Story 6 complete - control structures work independently

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T070 [P] Add PHPDoc comments to all public methods across src/
- [ ] T071 [P] Run php-cs-fixer on entire codebase
- [ ] T072 Verify 100% code coverage with PHPUnit
- [ ] T073 [P] Performance benchmarking for success criteria validation
- [ ] T074 Run quickstart.md validation scenarios
- [ ] T075 Final integration test covering all user stories in tests/Integration/FullEngineTest.php

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-8)**: All depend on Foundational phase completion
  - US1 (P1): No dependencies on other stories - MVP
  - US2 (P2): Depends on US1 (uses TemplateParser, TemplateRenderer)
  - US3 (P3): Depends on US1 (uses TemplateParser, TemplateRenderer)
  - US4 (P4): Depends on US2 (caches compiled templates)
  - US5 (P5): Depends on US1 (extends escaping behavior)
  - US6 (P6): Depends on US2 (uses DirectiveInterface pattern)
- **Polish (Phase 9)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational - **MVP SCOPE**
- **User Story 2 (P2)**: Can start after US1 - builds on parser/renderer
- **User Story 3 (P3)**: Can start after US1 - adds macro layer
- **User Story 4 (P4)**: Can start after US2 - caches compiled output
- **User Story 5 (P5)**: Can start after US1 - extends escaping
- **User Story 6 (P6)**: Can start after US2 - uses directive pattern

### Within Each User Story

- Tests MUST be written and FAIL before implementation
- Interfaces before implementations
- Core classes before integration
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel
- All Foundational exception classes (T010-T014) can run in parallel
- All interface files within a story can run in parallel
- Test files for a story marked [P] can run in parallel

---

## Parallel Example: User Story 1

```bash
# Launch all tests for User Story 1 together:
Task: T016 "Unit test for HtmlEscaper"
Task: T017 "Unit test for TemplateParser"
Task: T018 "Unit test for TemplateRenderer"

# Then launch interfaces in parallel:
Task: T020 "Create EscaperInterface"
Task: T022 "Create ParserInterface"
Task: T024 "Create RendererInterface"

# Then implementations (some parallel, some sequential):
Task: T021 "Create HtmlEscaper" (parallel)
Task: T023 "Create TemplateParser" (depends on T022)
Task: T025 "Create TemplateRenderer" (depends on T023, T024)
Task: T026 "Create AdvancedTemplateEngine" (depends on T025)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test basic rendering independently
5. Deploy/demo if ready - engine can render templates with variables

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → **MVP: Basic rendering**
3. Add User Story 2 → Test independently → **Inheritance support**
4. Add User Story 3 → Test independently → **Macro support**
5. Add User Story 4 → Test independently → **Production caching**
6. Add User Story 5 → Test independently → **Security complete**
7. Add User Story 6 → Test independently → **Full feature set**

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1 (MVP)
   - Developer B: Prepare US2 interfaces while A completes US1
3. After US1 complete:
   - Developer A: User Story 3 (Macros)
   - Developer B: User Story 2 (Inheritance)
4. After US2 complete:
   - Developer A: User Story 5 (Security)
   - Developer B: User Story 4 (Caching)
   - Developer C: User Story 6 (Control Structures)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- **TDD is mandatory**: Verify tests fail before implementing
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
