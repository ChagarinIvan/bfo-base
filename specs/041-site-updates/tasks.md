# Tasks: История обновлений сайта

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [UI contract](contracts/ui.md)

**Tests**: Required for changed behavior and historical catalog completeness.

## Phase 1: Setup

- [X] T001 Inventory every `specs/*/spec.md`, its Git fixation date, current SPA routes and replacement tasks; record editorial decisions in `specs/041-site-updates/research.md`.

## Phase 2: Foundation

- [X] T002 [P] Add catalog coverage, ordering and link-target tests in `resources/spa/pages/updates/updates.test.ts` before implementation.
- [X] T003 [P] Add guest/auth route and menu tests in `resources/spa/router/index.test.ts` and `resources/spa/components/AppLayout.test.ts` before implementation.

## Phase 3: User Story 1 - View history (P1) 🎯 MVP

**Goal**: Authenticated users can find a dated reverse-chronological updates page.

**Independent Test**: Direct guest access redirects to login; signed-in page opens from menu and newest item comes first.

- [X] T004 [US1] Register protected `/app/updates` route in `resources/spa/router/index.ts`.
- [X] T005 [US1] Add authenticated menu link and translation in `resources/spa/components/AppLayout.vue`, `resources/spa/components/navigationModels.ts`, and `resources/lang/by.json`.
- [X] T006 [US1] Render the dated static list in `resources/spa/pages/updates/UpdatesPage.vue` and style in `resources/spa/styles.css`.

## Phase 4: User Story 2 - Read details and navigate (P1)

**Goal**: Expand each record, see an example where useful, and follow current or replacement links.

**Independent Test**: Expand both a current and a replaced record, navigate to their valid targets, use keyboard disclosure.

- [X] T007 [US2] Add disclosure and link behavior tests in `resources/spa/pages/updates/UpdatesPage.test.ts` before finalizing detail rendering.
- [X] T008 [US2] Complete all 42 curated entries and stable sorting in `resources/spa/pages/updates/updates.ts`, including examples, current links and checked replacement-task links.
- [X] T009 [US2] Complete detailed rendering and keyboard/visual styling in `resources/spa/pages/updates/UpdatesPage.vue` and `resources/spa/styles.css`.

## Phase 5: User Story 3 - Keep future specs visible (P2)

**Goal**: Make the update record a required outcome of each future spec-kit feature.

**Independent Test**: Confirm catalog covers all spec directories and manifest requires an entry and link review.

- [X] T010 [US3] Add the publication requirement and review step to `.specify/memory/backend-architecture-manifest.md`.
- [X] T011 [US3] Verify every directory has exactly one catalog entry and every replacement link names a checked later task in `resources/spa/pages/updates/updates.test.ts`.

## Final Phase: Validation

- [X] T012 Reconcile acceptance scenarios and [quickstart.md](quickstart.md); run focused Vitest, then one final `npm run ci`, `composer cs`, `composer stan`, `composer rector -- --dry-run`, `composer test`; inspect SPA startup, route and N+1 impact.
- [X] T013 Mark all completed tasks and review `git diff --check`, the spec checklist and final feature coverage in `specs/041-site-updates/tasks.md`.

## Dependencies

T001 establishes source facts. T002–T003 fail first. T004–T006 deliver US1. T007 precedes T008–T009, which deliver US2. T010–T011 deliver US3. T012–T013 follow all stories.

## Parallel Opportunities

T002 and T003 touch separate tests and may be prepared together. Catalog editorial research and route implementation can be reviewed independently after T001. No parallel edits to `updates.ts` or `UpdatesPage.vue`.

## Implementation Strategy

Ship US1 first for access and ordering, then US2 for detailed navigation, then US3 for durable publication policy. Validate each story with focused tests and run the complete project gates once at the end.

## Validation record

- 2026-10-05: focused Vitest passed (27 tests); `npm run ci` passed (84 files, 245 tests, build).
- `composer cs`, `composer stan`, and `composer rector -- --dry-run` passed.
- `DB_DATABASE=bfo_base_test composer test` passed: 613 tests, 4402 assertions, 16 PHPUnit notices.
- Vite dev server started; direct `/app/updates` returned HTML 200. The existing Nginx `/app/*` fallback serves the SPA in production, so no Laravel route was added.
- Catalog coverage test found 42 unique entries for 42 spec directories. Route and replacement-task links passed validation. No new API/database query path or N+1 risk was introduced. `git diff --check` passed.
