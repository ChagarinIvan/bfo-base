# Tasks: Event View SPA

**Input**: Design documents from `/specs/021-event-view-spa/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/event-view-api.md

## Phase 1: Foundation

**Purpose**: Establish the read-model contracts and bounded query capabilities shared by the SPA page.

- [ ] T001 Add Event detail and event-distance Application DTOs, commands, services, API actions, and V1 routes in `app/Application/{Dto,Service}/{Event,Distance}/`, `app/Bridge/Laravel/Http/Controllers/Api/V1/{Event,Distance}/`, and `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`.
- [ ] T002 Add API request coverage for event detail and unpaginated event distances, including guest/authenticated serialization, invalid input, and missing event handling in `tests/Feature/Api/V1/{Event,Distance}/`.
- [ ] T003 Extend the existing Club port and Eloquent adapter with batch lookup by normalized names in `app/Domain/Club/ClubRepository.php` and `app/Infrastructure/Laravel/Eloquent/Club/EloquentClubRepository.php`.
- [ ] T004 Extend ProtocolLine search/resources/DTO/assembler/list service and Eloquent query for bounded `distanceId`, name filtering, raw result columns, opt-in batched club resolution, and no unnecessary Event/Competition resources in `app/Application/{Dto,Service}/ProtocolLine/`, `app/Domain/ProtocolLine/`, and `app/Infrastructure/Laravel/Eloquent/ProtocolLine/EloquentProtocolLinesRepository.php`.
- [ ] T005 Add API request and focused Application tests for distance/name scoping, result fields, club normalization, validation, and no per-line relationship queries in `tests/Feature/Api/V1/ProtocolLine/` and `tests/Application/Service/ProtocolLine/`.

**Checkpoint**: The API contracts can serve the SPA without the Blade render path or N+1 lookups.

---

## Phase 2: User Story 1 - View an event and its results (Priority: P1) 🎯 MVP

**Goal**: Visitors can open an event in the SPA, select an event distance, and browse its protocol lines.

**Independent Test**: Open `/app/events/:eventId`, select each available distance, and confirm its paginated results, empty state, and not-found flow.

- [ ] T006 [P] [US1] Add SPA Event/Distance/ProtocolLine types and event/distance/protocol-line API-client methods with Vitest coverage in `resources/spa/api/{types,events,distances,protocolLines}.{ts,test.ts}`.
- [ ] T007 [P] [US1] Add localized strings for event-view loading, error, empty, table, selector, and result labels in `resources/lang/by.json` and the SPA i18n tests if applicable.
- [ ] T008 [US1] Implement `EventViewPage` with event card, competition/cup links, distance selector, result table, loading/error/empty states, and paginator in `resources/spa/pages/events/EventViewPage.vue` and `resources/spa/pages/events/EventViewPage.test.ts`.
- [ ] T009 [US1] Register `/app/events/:eventId` and change competition event links to the SPA route in `resources/spa/router/{index,index.test}.ts` and `resources/spa/pages/competitions/{CompetitionDetailsPage,CompetitionDetailsPage.test}.vue`.

**Checkpoint**: Public event viewing is fully usable without Blade.

---

## Phase 3: User Story 2 - Use staff-only event result controls (Priority: P2)

**Goal**: Staff retain audit, edit, activation-date, and Assign person navigation without exposing them to guests.

**Independent Test**: Compare guest and authenticated event pages; staff can open legacy edit and every existing SPA assignment form.

- [ ] T010 [US2] Add authenticated-only impressions, legacy Edit action, activation-date result column, and Assign person Actions column to `resources/spa/pages/events/{EventViewPage.vue,EventViewPage.test.ts}`.
- [ ] T011 [US2] Add guest-versus-authenticated API/page assertions for staff-only event and protocol-line controls in `tests/Feature/Api/V1/Event/` and `resources/spa/pages/events/EventViewPage.test.ts`.

**Checkpoint**: Staff workflows are preserved and guest data/control boundaries are enforced.

---

## Phase 4: User Story 3 - Search and identify protocol-line affiliations (Priority: P3)

**Goal**: Visitors can filter selected-distance results by athlete name and follow only valid normalized club links.

**Independent Test**: Enter first/last-name fragments and verify list reload/reset; verify matching and unmatched raw club names render correctly.

- [ ] T012 [US3] Add debounced or explicit name-filter interaction that resets the selected-distance result page and preserves the distance in `resources/spa/pages/events/{EventViewPage.vue,EventViewPage.test.ts}`.
- [ ] T013 [US3] Render raw club names with optional resolved-club links and cover matched/unmatched cases in `resources/spa/pages/events/{EventViewPage.vue,EventViewPage.test.ts}`.

**Checkpoint**: Event-result discovery preserves the legacy club behavior without over-fetching.

---

## Phase 5: Retire the legacy event view

**Purpose**: Remove only the superseded public event-rendering surface after the SPA path is live.

- [ ] T014 Remove `ShowEventAction`, `ShowEventDistanceAction`, `RendersEventDistance`, `resources/views/events/show.blade.php`, their web-route imports/definitions, and uniquely obsolete tests in `app/Bridge/Laravel/Http/Controllers/Event/`, `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, `resources/views/events/`, and `tests/Bridge/Laravel/Http/Controllers/Event/`.
- [ ] T015 Add or update route-level regression coverage proving retired show URLs are absent and retained event maintenance/cup routes remain in `tests/Feature/`.

---

## Phase 6: Polish and validation

- [ ] T016 Run focused PHPUnit and Vitest suites for the changed API and SPA files; resolve failures in the affected files.
- [ ] T017 Run final `composer cs`, `composer rector -- --dry-run`, `XDEBUG_MODE=off composer stan --memory-limit=4G`, `composer test`, and `npm run ci`; inspect the diff and confirm the event view adds no N+1 queries.
- [ ] T018 Validate every quickstart scenario in `specs/021-event-view-spa/quickstart.md` and mark completed tasks in this file.

## Dependencies & Execution Order

- T001–T005 are foundational and precede the SPA work.
- T006–T009 deliver the public MVP and depend on T001–T005.
- T010–T011 build on the event page; T012–T013 build on its result table.
- T014–T015 occur only after T009 confirms all public event links use the SPA.
- T016–T018 complete after every implementation task.

## Parallel Opportunities

- T002 and T003 can begin alongside T001 where files do not overlap.
- T006 and T007 can run in parallel after the API shape is established.
- T010 and T012/T013 share `EventViewPage` and therefore run sequentially.

## Implementation Strategy

Deliver T001–T009 first for a public replacement. Add staff controls, then filters/club links, then remove Blade entry points only once the SPA route is covered. Finish with focused tests and the final quality gate.
