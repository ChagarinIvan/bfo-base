---

description: "Задачи реализации: управление соревнованиями в SPA"
---

# Tasks: Управление соревнованиями в SPA

**Input**: Артефакты из `specs/005-competition-spa-management/`

**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md),
[data-model.md](data-model.md), [contracts](contracts/) и [quickstart.md](quickstart.md)

**Tests**: Каждое изменение поведения покрывается PHPUnit request/unit и Vitest. Во время
итераций запускать только затронутые тесты; полный `composer test` — в финальной фазе либо после
крупного интеграционного шага.

**Organization**: Задачи сгруппированы по user story, чтобы каждый пользовательский срез можно
было реализовать и проверить самостоятельно.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Подготовить типизированные SPA-границы без новых зависимостей.

- [X] T001 [P] Add shared competition and event transport types in `resources/spa/api/types.ts`.
- [X] T002 [P] Add Belarusian SPA labels, validation, empty-state, deletion, and navigation strings in `resources/spa/i18n.ts`.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Подготовить общие SPA-модели, не меняя Blade routes.

- [X] T003 Extend `resources/spa/pages/competitions/competitionModels.ts` with camelCase V1 query construction, debounce, pagination-reset, date-range, and API-error helpers used by competition list, details, and form pages.
- [X] T004 Add shared helper coverage in `resources/spa/pages/competitions/competitionModels.test.ts` for camelCase query construction, debounce/minimum-name behaviour, pagination reset, and field-error mapping.

**Checkpoint**: Existing V1/SPA foundation remains compatible; feature slices can now use common API routes, types, translations, and page helpers.

---

## Phase 3: User Story 1 — Найти соревнование (Priority: P1) 🎯 MVP

**Goal**: Посетитель фильтрует постраничный список активных соревнований по году, названию и
дате без перезагрузки страницы.

**Independent Test**: Без авторизации выбрать год, найти по имени из трёх символов, выбрать
пограничную дату многодневного соревнования и убедиться, что все фильтры применены одновременно.

### Tests for User Story 1

- [X] T005 [P] [US1] Extend API request coverage for camelCase `perPage`, trim-before-validation, case-insensitive matching, name/date filters, combined filters, short-name `422`, pagination headers, and active-only results in `tests/Feature/Api/V1/Competition/ListCompetitionsActionTest.php`.
- [X] T006 [P] [US1] Extend filter and debounce coverage in `resources/spa/pages/competitions/CompetitionsPage.test.ts`.

### Implementation for User Story 1

- [X] T007 [US1] Add DTO-specific request normalization before validation in `app/Application/Dto/AbstractDto.php` and `app/Bridge/Laravel/Http/Controllers/ApiAction.php`, then extend `SearchCompetitionDto` and `Pagination` request mapping for trimmed `name`, `year`, `date`, and camelCase `perPage` in `app/Application/Dto/Competition/CompetitionSearchDto.php` and `app/Application/Dto/Pagination/Pagination.php` without adding an `active` API field.
- [X] T008 [US1] Preserve false-safe criteria construction while passing the expanded search DTO through `app/Application/Service/Competition/ListCompetitions.php`.
- [X] T009 [US1] Implement active-only case-insensitive year/name/date filtering with inclusive `from <= date <= to` matching in `app/Infrastructure/Laravel/Eloquent/Competition/EloquentCompetitionRepository.php`.
- [X] T010 [US1] Keep the V1 list action thin and pass the expanded DTO/pagination flow through `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/ListCompetitionsAction.php`.
- [X] T011 [US1] Add name input with 300ms debounce, date filter, local minimum-length feedback, filter-aware pagination reset, and typed API loading in `resources/spa/pages/competitions/CompetitionsPage.vue`.

**Checkpoint**: User Story 1 is independently usable from `/app/competitions`; invalid short requests receive `422`, and no inactive competition is exposed.

---

## Phase 4: User Story 2 — Просмотреть соревнование и его этапы (Priority: P1)

**Goal**: Посетитель открывает SPA-детали активного соревнования и его активные этапы without
N+1; переход к отдельному этапу остаётся legacy link.

**Independent Test**: Открыть активное соревнование с несколькими этапами, проверить поля
таблицы и `participantsCount`; проверить пустой список этапов, включая inactive/missing competition.

### Tests for User Story 2

- [X] T012 [P] [US2] Add request coverage for public active-only single-competition responses and missing/inactive `404` in `tests/Feature/Api/V1/Competition/ViewCompetitionActionTest.php`.
- [X] T013 [P] [US2] Add request coverage for camelCase `competitionId`, validation, active-only events, protocol-line counts, empty states including missing/inactive competition, and authenticated `created`/`updated` impressions in `tests/Feature/Api/V1/Event/ListEventsActionTest.php`.
- [X] T014 [P] [US2] Cover paginated compact V1 DTO assembly and the preserved legacy Blade DTO path in `tests/Application/Service/Event/ListEventsServiceTest.php` and `tests/Application/Service/Event/ListLegacyEventsServiceTest.php`.
- [X] T015 [P] [US2] Add SPA detail page and route coverage in `resources/spa/pages/competitions/CompetitionDetailsPage.test.ts` and `resources/spa/router/index.test.ts`.

### Implementation for User Story 2

- [X] T016 [US2] Rename `EventSearchDto` to `SearchEventDto`, accept V1 camelCase `competitionId`, and update all PHP consumers in `app/Application/Dto/Event/SearchEventDto.php`, `app/Application/Service/Event/ListEvents.php`, `app/Application/Handler/Event/DisableCompetitionHandler.php`, and affected Bridge actions/tests.
- [X] T017 [US2] Add compact scalar `ViewEventDto` and `EventAssembler::toViewEventDto()` in `app/Application/Dto/Event/ViewEventDto.php` and `app/Application/Dto/Event/EventAssembler.php`, keeping `LegacyViewEventDto` unchanged for Blade.
- [X] T018 [US2] Rename the Blade service to `ListLegacyEventsService`, then make `ListEventsService::execute(ListEvents $command)` return `Slice<ViewEventDto>` through the V1 command/service path.
- [X] T019 [US2] Add active-only `EventRepository::paginate()` with one protocol-line relation count for the V1 list path in `app/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepository.php`, without loading flags or cups into the V1 projection.
- [X] T020 [US2] Add public V1 `ViewCompetitionAction` and `ListEventsAction` in `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/ViewCompetitionAction.php` and `app/Bridge/Laravel/Http/Controllers/Api/V1/Event/ListEventsAction.php`; `ListEventsAction` follows DTO + pagination → command → V1 service → assembler flow and returns an empty serialized Slice when no active events match `competitionId`.
- [X] T021 [US2] Register the new public competition and event read actions in `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php` and preserve optional Bearer impressions.
- [X] T022 [P] [US2] Implement typed get-one and event-list calls in `resources/spa/api/competitions.ts` and `resources/spa/api/events.ts`.
- [X] T023 [US2] Add `CompetitionDetailsPage.vue` with loading/error/empty states, event table, and legacy event links in `resources/spa/pages/competitions/CompetitionDetailsPage.vue`.
- [X] T024 [US2] Add detail route and replace competition-name legacy href with the SPA route in `resources/spa/router/index.ts` and `resources/spa/pages/competitions/CompetitionsPage.vue`.

**Checkpoint**: User Story 2 is independently usable from a list item; V1 sends only scalar Event projection fields and the event query has a bounded query count.

---

## Phase 5: User Story 3 — Управлять соревнованием (Priority: P2)

**Goal**: Аутентифицированный пользователь редактирует или мягко удаляет активное соревнование
в SPA, а неавторизованный не получает ни UI-действий, ни доступа к endpoints.

**Independent Test**: Войти, открыть edit, сохранить валидную форму, подтвердить delete и
проверить отсутствие записи в list/detail; повторить PUT/DELETE без Bearer и получить `401`.

### Tests for User Story 3

- [X] T025 [P] [US3] Add V1 request coverage for authenticated PUT/DELETE, PUT JSON body with V1 camelCase fields only, shared form validation, `401`, missing/inactive `404`, and soft-delete persistence in `tests/Feature/Api/V1/Competition/UpdateCompetitionActionTest.php` and `tests/Feature/Api/V1/Competition/DeleteCompetitionActionTest.php`.
- [X] T026 [P] [US3] Extend command/service unit coverage for update and disable behaviour in `tests/Application/Service/Competition/UpdateCompetitionInfoServiceTest.php` and `tests/Application/Service/Competition/DisableCompetitionServiceTest.php`.
- [X] T027 [P] [US3] Add form, action-menu, confirmation, and edit-page SPA coverage in `resources/spa/pages/competitions/CompetitionForm.test.ts`, `resources/spa/components/actions/CompetitionActionMenu.test.ts`, `resources/spa/components/actions/ConfirmDeleteDialog.test.ts`, and `resources/spa/pages/competitions/EditCompetitionPage.test.ts`.

### Implementation for User Story 3

- [X] T028 [P] [US3] Add authenticated V1 `UpdateCompetitionAction` and `DeleteCompetitionAction` in `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/UpdateCompetitionAction.php` and `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/DeleteCompetitionAction.php`, reusing `UpdateCompetitionInfo` and `DisableCompetition` commands/services.
- [X] T029 [US3] Register protected PUT and DELETE competition routes with `AuthenticateApiV1` in `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`.
- [X] T030 [P] [US3] Add typed get/update/delete API calls and error conversion in `resources/spa/api/competitions.ts` and `resources/spa/api/types.ts`.
- [X] T031 [P] [US3] Extract the shared create/edit fields and field-error handling into `resources/spa/pages/competitions/CompetitionForm.vue`.
- [X] T032 [P] [US3] Create reusable actions menu and explicit deletion confirmation dialog in `resources/spa/components/actions/CompetitionActionMenu.vue` and `resources/spa/components/actions/ConfirmDeleteDialog.vue`.
- [X] T033 [US3] Refactor creation to use the shared form in `resources/spa/pages/competitions/CreateCompetitionPage.vue`.
- [X] T034 [US3] Add prefilled edit page, auth route guard, save/navigation behaviour, row actions, and delete refresh/redirect behaviour in `resources/spa/pages/competitions/EditCompetitionPage.vue`, `resources/spa/pages/competitions/CompetitionsPage.vue`, `resources/spa/pages/competitions/CompetitionDetailsPage.vue`, and `resources/spa/router/index.ts`.

**Checkpoint**: User Story 3 is independently testable with a Bearer token; no mutation is possible without authentication and deleted records remain in storage only.

---

## Phase 6: User Story 4 — Перемещаться между SPA и существующими разделами (Priority: P3)

**Goal**: SPA navbar reflects every reachable Blade navbar item, choosing SPA only for migrated
routes and existing URLs for all other sections.

**Independent Test**: Проверить navbar до и после входа; открыть каждый public/authenticated
пункт и убедиться, что он ведёт в корректный SPA или legacy раздел.

### Tests for User Story 4

- [X] T035 [US4] Add public/authenticated hybrid-navbar coverage in `resources/spa/components/AppLayout.test.ts` and legacy/SPА route coverage in `resources/spa/router/index.test.ts`.

### Implementation for User Story 4

- [X] T036 [US4] Build grouped public and authenticated navigation in `resources/spa/components/AppLayout.vue` for competitions, cups, persons, clubs, ranks, groups, FAQ, API FAQ, registration, login, and logout using documented SPA/legacy destinations.
- [X] T037 [US4] Add responsive dropdown and legacy-link styling in `resources/spa/styles.css` without changing `resources/views/layouts/navbar.blade.php`.

**Checkpoint**: User Story 4 is independently usable; visitors never see private links and every visible link has a working destination.

---

## Phase 7: User Story 5 — Собрать повторно используемый интерфейс (Priority: P4)

**Goal**: Повторяющиеся icons, buttons, action controls, confirmation, and impressions are simple
reusable SPA components rather than copied logic.

**Independent Test**: Сравнить list, detail и form pages: одинаковые action controls and audit
details render consistently and their unit tests cover shared state.

### Tests for User Story 5

- [X] T038 [P] [US5] Extend reusable component tests in `resources/spa/components/actions/CompetitionActionMenu.test.ts`, `resources/spa/components/actions/ConfirmDeleteDialog.test.ts`, and `resources/spa/components/impressionModels.test.ts`.

### Implementation for User Story 5

- [X] T039 [US5] Consolidate reusable icon-button, actions-menu, confirmation, and impression usage in `resources/spa/components/actions/`, `resources/spa/components/ImpressionDetails.vue`, and all competition pages without introducing unnecessary wrapper components.
- [X] T040 [US5] Remove duplicated competition page interaction/style code from `resources/spa/pages/competitions/*.vue` and `resources/spa/styles.css` after shared components are adopted.

**Checkpoint**: Shared interactions have one implementation, while presentation-specific page code stays local.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Verify the complete vertical slice, quality gates, compatibility, and documentation.

- [X] T041 [P] Update implementation notes and manual scenarios in `specs/005-competition-spa-management/quickstart.md` if actual endpoint/component names differ from the approved contracts.
- [X] T042 Run targeted suites for `tests/Feature/Api/V1/Competition/`, `tests/Feature/Api/V1/Event/`, `tests/Application/Service/{Competition,Event}/`, and `resources/spa/**/*.test.ts`, then run `npm run ci` and record failures before continuing.
- [X] T043 Run final `composer cs`, `composer stan`, `composer rector`, and `composer test` from `composer.json` using `.php-cs-fixer.php` and `phpstan.neon`; review only resulting feature diffs.
- [X] T044 Verify V1 API responses, SPA deep links, and legacy navbar destinations against `specs/005-competition-spa-management/contracts/` and `specs/005-competition-spa-management/quickstart.md`; once during development inspect the Event list query listener or query log for one and several events to confirm there is no N+1.

### Phase 8 validation log

- `npm run ci`: passed (lint, typecheck, 17 Vitest files / 45 tests, SPA build).
- Targeted PHP suite: 43/46 passed on the first run; the remaining 3 errors were caused by the shared MySQL test database being left without a consistent `migrations` table. The complete rerun and final suite passed.
- `composer cs`, `composer stan`, `composer rector -- --dry-run`: passed.
- `composer test`: passed (346 tests, 2632 assertions).
- API routes, SPA deep links, documented legacy destinations, and the Event `withCount('protocolLines')` projection were checked; no per-event flags/cups loading or N+1 path was found.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Starts immediately.
- **Foundational (Phase 2)**: Depends on Phase 1 and supplies shared API/page helpers.
- **US1 (Phase 3)**: Depends on Phase 2; is the MVP.
- **US2 (Phase 4)**: Depends on Phase 2 and uses the list route/API types introduced by US1 for the in-app entry point.
- **US3 (Phase 5)**: Depends on US1 and US2 because it adds row/detail actions and reuses the shared form.
- **US4 (Phase 6)**: Depends on US3; it follows detail/edit route work and reuses the settled router tests.
- **US5 (Phase 7)**: Depends on US4 because it consolidates the completed shared controls and navigation presentation.
- **Polish (Phase 8)**: Depends on all selected user stories.

### User Story Dependencies

```text
Setup → Foundational → US1 (MVP) → US2 → US3 → US4 → US5 → Polish
```

### Parallel Opportunities

- In US1, T005 and T006 can be written in parallel; T007–T010 are backend-only and T011 is frontend-only after their shared contracts settle.
- In US2, T012–T015 are independent test files; T020 and T022 can proceed once DTO/assembler interfaces are agreed.
- In US3, T025–T027 and T028/T030/T031/T032 touch separate files; serialise T033–T034 after shared form/actions exist.
- User stories themselves remain sequential: complete and validate one checkpoint before starting the next story.

## Implementation Strategy

### MVP First

1. Complete Phases 1–2.
2. Complete US1 (T005–T011).
3. Run its targeted request/Vitest tests and demo `/app/competitions` filtering.

### Incremental Delivery

1. Add US2 for public competition detail and bounded events API.
2. Add US3 for authenticated edit/delete and the shared form/actions it requires.
3. Add US4 hybrid navigation and US5 cleanup of the reusable UI primitives.
4. Run final quality gates only after the full feature slice is integrated.

## Notes

- `[P]` means the task uses distinct files and may be parallelised after stated dependencies.
- Every story task carries its `[US#]` label; setup, foundational, and polish tasks intentionally do not.
- No task creates a new `*Service` or `*Repository`: V1 actions reuse existing commands/services and assemblers, while new DTOs are Application read models.
- The active-record rule remains repository-owned; `active` is not added to V1 query parameters or SPA types.
