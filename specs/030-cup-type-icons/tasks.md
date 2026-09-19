# Tasks: Cup Type Icons in SPA

**Input**: Design documents from `/specs/030-cup-type-icons/`

**Tests**: Required by FR-009 and project TDD rules.

## Phase 1: Setup

- [X] T001 Confirm current `CupType` values, SPA icon loading, and preserve the dirty worktree in `research.md`
- [X] T002 [P] Add Belarusian fallback/type label keys in `resources/lang/by.json`
- [X] T003 [P] Load the bundled Font Awesome CSS in `resources/spa/main.ts`

## Phase 2: Foundational

- [X] T004 [P] Add typed `CupType` icon definitions and fallback lookup in `resources/spa/components/cupTypeModels.ts`
- [X] T005 [P] Add reusable accessible icon component in `resources/spa/components/CupTypeIcon.vue`
- [X] T006 [P] Add mapping unit tests for all enum values and unknown fallback in `resources/spa/components/cupTypeModels.test.ts`
- [X] T007 Add icon component tests for label/title and fallback behavior in `resources/spa/components/CupTypeIcon.test.ts`

## Phase 3: User Story 1 - Recognize cup type in listing (Priority: P1)

**Independent test**: Cups listing renders the mapped icon and accessible type label beside every cup name while preserving its link.

- [X] T008 [P] [US1] Extend cups listing tests with multiple types, icon classes, labels, and unknown fallback in `resources/spa/pages/cups/CupsPage.test.ts`
- [X] T009 [US1] Render `CupTypeIcon` beside the cup name in `resources/spa/pages/cups/CupsPage.vue`
- [X] T010 [US1] Verify listing CSS/layout keeps icon and link aligned in `resources/spa/styles.css`

## Phase 4: User Story 2 - Select cup type with visual guidance (Priority: P1)

**Independent test**: Shared create/edit form renders icon-bearing options, preserves selected enum value, and emits the unchanged payload.

- [X] T011 [P] [US2] Extend shared cup form tests for all type options, selected icon, fallback, and emitted enum payload in `resources/spa/pages/cups/CupForm.test.ts`
- [X] T012 [US2] Replace plain type options with icon-bearing PrimeVue Select option/value slots in `resources/spa/pages/cups/CupForm.vue`
- [X] T013 [US2] Verify create/edit pages continue to use the shared form and existing API payload in `resources/spa/pages/cups/CreateCupPage.vue` and `resources/spa/pages/cups/EditCupPage.vue`

## Phase 5: User Story 3 - Reuse mapping for future pages (Priority: P2)

**Independent test**: Mapping and component are importable outside the cups page without API or backend changes.

- [X] T014 [P] [US3] Add a documented reusable export contract in `resources/spa/components/cupTypeModels.ts`
- [X] T015 [US3] Verify no event-page integration or API payload change is introduced and update `quickstart.md` if needed

## Phase 6: Polish and verification

- [X] T016 [P] Run focused Vitest tests for mapping, icon, form, and listing
- [X] T017 Run `npm run typecheck`, `npm run ci`, and `git diff --check`
- [X] T018 Verify every task is marked complete and acceptance scenarios in `quickstart.md` pass

## Dependencies & Execution Order

- T001–T007 are foundational; T008–T010 and T011–T013 follow the mapping/component work.
- T014–T015 depend on the shared mapping and component from Phase 2.
- T016–T018 run after all stories are implemented.
- T002, T003, T004, T005, and T006 can proceed in parallel where files do not overlap.
- Tests are written before the corresponding listing/form implementation tasks.

## Implementation Strategy

1. Establish and test the centralized mapping and accessible icon component.
2. Integrate icons into the listing as the MVP.
3. Integrate the same component into the shared create/edit selector.
4. Run the complete frontend quality gates and verify no API/backend behavior changed.
