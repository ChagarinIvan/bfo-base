# Tasks: Cup Event View SPA

**Input**: Design documents from `/specs/034-cup-event-view-spa/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Required by FR-010 and the project constitution.

## Phase 1: Setup

- [X] T001 Record existing legacy view, calculator dependencies, routing and cup-table destination in `specs/034-cup-event-view-spa/research.md`
- [X] T002 [P] Add Belarusian context, standings, group-selector and empty/error text in `resources/lang/by.json`

## Phase 2: Foundational V1 read model

- [X] T003 Reuse existing public cup-event, cup and event DTO contracts for the card in `app/Application/Dto/{Cup,CupEvent}/`
- [X] T004 Define normalized standings-search command and Application query service in `app/Application/{Dto,Service}/CupEvent/`
- [X] T005 Reuse active-cup event repository reads and the existing scoring calculator without adding legacy persistence code
- [X] T006 Add the public V1 standings action and route in `app/Bridge/Laravel/{Http/Controllers/Api/V1/Cup,Provider}/`
- [X] T007 [P] Cover calculated standings through real scoring fixtures in `tests/Feature/Api/V1/Cup/ListCupEventPointsActionTest.php`
- [X] T008 [P] Add request tests with action `@see` for public validation, pagination and 404 in `tests/Feature/Api/V1/Cup/ListCupEventPointsActionTest.php`

**Checkpoint**: The SPA has public, validated context and paginated standings contracts with no manual JSON or API N+1.

## Phase 3: User Story 1 - View cup-event standings (Priority: P1) 🎯 MVP

**Goal**: Visitors use one SPA card and a selected-group standings table.

**Independent Test**: Load an existing cup event, choose a group, search an athlete, paginate, and verify empty/not-found behaviour.

- [X] T009 [P] [US1] Add CupEvent point TypeScript contracts and HTTP client tests in `resources/spa/api/{types,cups}.{ts,test.ts}`
- [X] T010 [P] [US1] Reuse shared listing query debounce/reset helpers in `resources/spa/pages/listingModels.ts`
- [X] T011 [US1] Add load and first-group standings page tests in `resources/spa/pages/cups/CupEventViewPage.test.ts`
- [X] T012 [US1] Implement the shared-card and 50-row ListingTable experience in `resources/spa/pages/cups/CupEventViewPage.vue`
- [X] T013 [US1] Register the public `/app/cup-events/:cupEventId` route in `resources/spa/router/index.ts`

**Checkpoint**: A visitor can independently view one selected cup-event group's standings in SPA.

## Phase 4: User Story 2 - Reach the correct cup-event view (Priority: P1)

**Goal**: Cup stages link to their calculated cup-event page.

**Independent Test**: Follow a stage link from a cup table and assert its target is the cup-event SPA route; verify card event/competition links.

- [X] T014 [US2] Preserve CupViewPage regression coverage while changing its destination in `resources/spa/pages/cups/CupViewPage.test.ts`
- [X] T015 [US2] Change the cup-stage table link to the new cup-event route in `resources/spa/pages/cups/CupViewPage.vue`
- [X] T016 [US2] Cover cup-event card links to existing competition/event SPA routes in `resources/spa/pages/cups/CupEventViewPage.test.ts`

**Checkpoint**: The reported cup-table navigation defect is fixed without changing standalone event navigation.

## Phase 5: User Story 3 - Retire the legacy standings surface (Priority: P2)

**Goal**: The SPA is the sole cup-event standings render surface.

**Independent Test**: The old URL has no route/view; retained cup table, export, cache and mutation routes remain registered.

- [X] T017 [US3] Add route-retirement regression coverage in `tests/Feature/Cup/CupEventViewRetirementTest.php`
- [X] T018 [US3] Delete `ShowCupEventGroupAction`, its web route/import, Blade template and obsolete tests in `app/Bridge/Laravel/{Http/Controllers/Cup,Provider}/`, `resources/views/cup/events/`, and `tests/Bridge/Laravel/Http/Controllers/CupEvents/`
- [X] T019 [US3] Search usages and retain legacy `app/Services` still used by cup table/export flows

**Checkpoint**: No legacy cup-event group render or uniquely dead legacy support remains.

## Amendment: request lifecycle and table feedback

- [X] T020 [US1] Add `AbortSignal` support to cup, event, club, user and points API clients; abort superseded cup-event loads and use request IDs to discard obsolete responses in `resources/spa/api/` and `resources/spa/pages/cups/CupEventViewPage.vue`
- [X] T021 [US1] Clear stale points and render the localized table error state after a non-cancellation points request failure in `resources/spa/pages/cups/CupEventViewPage.vue`
- [X] T022 [US1] Prevent one- and two-character athlete searches from requesting points, show the shared Belarusian minimum-length hint, and cover it in `resources/spa/pages/cups/CupEventViewPage.test.ts`
- [X] T023 [P] Add shared Message sizing for loading, error and empty ListingTable states in `resources/spa/components/ListingTable.vue` and `resources/spa/styles.css`

## Phase 6: Polish and verification

- [ ] T024 Run focused PHPUnit and Vitest suites for new points reads, cup links, router, page, request lifecycle and legacy retirement
- [ ] T025 Run `composer cs`, `composer stan`, `composer rector -- --dry-run`, `composer test`, `npm run ci`, and `git diff --check`; inspect queries for N+1 and response bounds
- [X] T026 Reconcile implementation against `spec.md`, `plan.md`, `contracts/`, `quickstart.md` and record delivered request-lifecycle work

## Dependencies & Execution Order

- T001–T008 establish the read contract and block SPA work.
- T009–T013 deliver the independently usable standings page.
- T014–T016 fix the cup-table navigation after the page route exists.
- T017–T019 remove the Blade surface only after SPA coverage passes.
- T024–T026 complete the feature after all stories.

## Parallel Opportunities

- T002, T007 and T008 can proceed independently after the corresponding model design is stable.
- T009 and T010 use distinct SPA files after the API contract is defined.
- Backend and SPA focused test runs can execute in parallel after their changes land.

## Implementation Strategy

1. Establish a narrow public V1 read model before any SPA UI work.
2. Deliver the group-selected paginated standings page first.
3. Redirect cup-stage navigation to the new public route.
4. Delete the legacy rendering path only after direct coverage proves replacement and retained routes.
