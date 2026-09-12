# Tasks: Retire Event Blade

**Input**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md), [data-model.md](data-model.md), [event-management-api.md](contracts/event-management-api.md)

**Tests**: Behaviour changes require API/integration tests and Vitest coverage; Application services use mocked ports/collaborators rather than Eloquent factories.

## Phase 1: Foundation

**Purpose**: Establish the authenticated event mutation contract before SPA work.

- [ ] T001 [P] Add API request coverage for create, update, deactivate, unauthenticated access, and validation in `tests/Feature/Api/V1/Event/EventManagementActionTest.php`
- [X] T002 [P] Add unit coverage for the extracted unite-events Application command/service and domain factory/data service in `tests/Application/Service/Event/UniteEventsServiceTest.php` and `tests/Domain/Event/Factory/UniteFactoryTest.php`
- [X] T003 Add Event API V1 routes and action classes for create, update, deactivate, and unite in `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php` and `app/Bridge/Laravel/Http/Controllers/Api/V1/Event/`
- [X] T004 Move protocol-upload request concerns out of the legacy Event Web controller folder into API-compatible DTO/support code in `app/Application/Dto/Event/` and `app/Bridge/Laravel/Http/`
- [X] T005 Implement API-backed create, update, and deactivate flows by reusing or renaming the target Application commands/services in `app/Application/Service/Event/` and returning `ViewEventDto`
- [X] T006 Extract the unite-events algorithm from `app/Bridge/Laravel/Http/Controllers/Event/UnitEventsAction.php` into an Application service plus `UniteFactory`/`UniteEventDataService`; lock source events, persist the new event through `EventRepository`, then generate relations while preserving aggregation and placement rules

**Checkpoint**: Authenticated API operations work without the legacy Web Event controller folder.

---

## Phase 2: User Story 1 - Manage an event without Blade (Priority: P1) 🎯 MVP

**Goal**: An organiser can create and edit event details and protocol sources from consistently styled SPA forms.

**Independent Test**: From a competition page, create and edit an event with upload or URL source and verify field-level validation, toast feedback, and SPA redirects.

- [X] T007 [P] [US1] Add event client multipart helpers and request/response types in `resources/spa/api/events.ts` and `resources/spa/api/types.ts`
- [X] T008 [P] [US1] Add API client tests for event create/update request shape in `resources/spa/api/events.test.ts`
- [X] T009 [P] [US1] Add unit/component tests for reusable event form validation and source selection in `resources/spa/pages/events/EventForm.test.ts`
- [X] T010 [US1] Implement the reusable event form using existing SPA card, field, date, message, button, loading, and error conventions in `resources/spa/pages/events/EventForm.vue`
- [X] T011 [US1] Implement create and edit event SPA pages with server validation, toasts, and redirects in `resources/spa/pages/events/CreateEventPage.vue` and `resources/spa/pages/events/EditEventPage.vue`
- [X] T012 [US1] Register authenticated event create/edit routes and replace legacy event create/edit links in `resources/spa/router/index.ts` and `resources/spa/pages/competitions/CompetitionDetailsPage.vue`
- [X] T012a [US1] Keep protocol source resolution in the Domain `ProtocolFactory`, translate invalid protocol content to the Application `invalid_protocol` HTTP 400 error, and cover file/URL/error paths in Domain/Application tests
- [ ] T013 [US1] Add Belarussian SPA translation strings for event form labels, validation, success, and failure states in `resources/spa/i18n/`

**Checkpoint**: Create and edit event workflows are fully SPA-based and independently testable.

---

## Phase 3: User Story 2 - Perform remaining event operations from the SPA (Priority: P2)

**Goal**: An organiser can deactivate an event and unite competition events through the SPA.

**Independent Test**: Authenticated organiser completes confirmed deactivation and a same-competition unite operation; guests see none of these controls.

- [X] T014 [P] [US2] Add EventViewPage tests for authenticated deactivate control and confirmation behaviour in `resources/spa/pages/events/EventViewPage.test.ts`
- [ ] T015 [P] [US2] Add unite-events page tests for selection, minimum-two validation, API failure, and success navigation in `resources/spa/pages/events/UniteEventsPage.test.ts`
- [X] T016 [US2] Add an icon-bearing confirmed deactivate action to the authenticated event view using existing action-menu/dialog patterns in `resources/spa/pages/events/EventViewPage.vue`
- [X] T017 [US2] Implement the SPA unite-events selector and mutation feedback in `resources/spa/pages/events/UniteEventsPage.vue`
- [X] T018 [US2] Register the authenticated unite route and replace the legacy sum link in `resources/spa/router/index.ts` and `resources/spa/pages/competitions/CompetitionDetailsPage.vue`
- [ ] T019 [US2] Add translations for deactivate confirmation and unite-event UI in `resources/spa/i18n/`

**Checkpoint**: Every retained event operation is reachable from SPA navigation.

---

## Phase 4: User Story 3 - Retire the obsolete event presentation stack (Priority: P3)

**Goal**: The legacy event Blade surface and its obsolete presentation path are gone while Cup Blade pages continue to render.

**Independent Test**: Inspect routes/files, request retired URLs, and open covered Cup pages; no legacy event Web action or `LegacyViewEventDto` remains.

- [ ] T020 [P] [US3] Update Cup DTO/assembler tests for `ViewEventDto` event data in `tests/Application/Dto/Cup/CupAssemblerTest.php` and `tests/Application/Dto/CupEvent/CupEventAssemblerTest.php`
- [X] T021 [P] [US3] Add regression coverage that retired event Web routes are absent and public event API/view routes remain in `tests/Feature/Api/V1/Event/EventManagementActionTest.php`
- [X] T022 [US3] Replace `LegacyViewEventDto` with `ViewEventDto` in Cup/CupEvent DTO assemblers and retire the obsolete distance-presence conditional in `app/Application/Dto/Cup/`, `app/Application/Dto/CupEvent/`, and `resources/views/cup/events/show.blade.php`
- [X] T023 [US3] Remove event-only legacy presentation mapping, list service, and their tests from `app/Application/Dto/Event/`, `app/Application/Service/Event/`, and `tests/Application/Service/Event/`
- [X] T024 [US3] Remove `resources/views/events/`, `app/Bridge/Laravel/Http/Controllers/Event/`, associated Web imports/routes, and obsolete controller tests from `resources/views/events/`, `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, and `tests/Bridge/Laravel/Http/Controllers/Event/`
- [X] T025 [US3] Search for stale event Blade paths, legacy Web controller names, and `LegacyViewEventDto`, then remove dead bindings/repository methods only when they have no callers in `app/` and `tests/`

**Checkpoint**: No event Blade surface remains; Cup Blade flow is still supported by `ViewEventDto`.

---

## Phase 5: Validation and delivery

- [ ] T026 Run focused PHP and Vitest suites from [quickstart.md](quickstart.md) and repair any feature regression
- [ ] T027 Run `composer cs`, `composer rector -- --dry-run`, `composer stan --memory-limit=4G`, `composer test`, `npm run ci`, and `git diff --check`
- [X] T028 Update task markers and reconcile [spec.md](spec.md), [plan.md](plan.md), [data-model.md](data-model.md), [contracts/event-management-api.md](contracts/event-management-api.md), and [quickstart.md](quickstart.md) with the delivered behaviour

## Dependencies & Execution Order

- T001–T006 establish the API and Application foundation.
- US1 (T007–T013) depends on T001–T005.
- US2 (T014–T019) depends on T003, T005, and T006; it can otherwise proceed independently from US1.
- US3 (T020–T025) depends on the new API/SPA replacements from US1 and US2.
- T026–T028 depend on all prior tasks.

## Parallel Opportunities

- T001 and T002 touch distinct test layers.
- T007–T009 and T014–T015 can be prepared in parallel once their contracts are settled.
- T020 and T021 touch distinct regression surfaces.

## Implementation Strategy

Deliver the API/Application foundation first, then event create/edit as the MVP. Add remaining organiser operations next, and delete the legacy surface only after its replacements and Cup DTO migration are covered. Finish with the complete project checks once.
