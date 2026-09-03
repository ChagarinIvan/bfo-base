---

description: "Actionable implementation tasks for the persons SPA migration"
---

# Tasks: SPA-страница персонов

**Input**: Design documents from `/specs/012-persons-spa/`

**Prerequisites**: `plan.md`, `spec.md`, `research.md`, `data-model.md`, `contracts/`,
`quickstart.md`

**Tests**: Included because the specification requires automated coverage for API contracts,
SPA behavior, route removal and legacy-route preservation. Follow the repository rule to write
tests before the corresponding implementation and keep Application/Domain unit tests free of
Eloquent entities.

**Organization**: Tasks are grouped by user story. US1 is the independent MVP; US2 adds legacy
actions; US3 completes shared-table reuse and removes the old list boundary.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Confirm the existing SPA/build boundaries and prepare feature-specific test locations.

- [X] T001 Verify the existing SPA entrypoint, `/app/*` Nginx fallback, API proxy, and available validation commands in `vite.config.ts`, `enviroment/nginx/conf.d/app.conf.example`, `package.json`, and `composer.json`
- [X] T002 [P] Create the persons-page frontend test locations and shared mock conventions in `resources/spa/pages/persons/PersonsPage.test.ts`, `resources/spa/pages/persons/personsModels.test.ts`, and `resources/spa/components/PersonTable.test.ts`

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Establish shared contracts and utilities required by the person list and club details.

**⚠️ CRITICAL**: No user story implementation should begin until this phase is complete.

- [X] T003 [P] Extend shared TypeScript contracts with `PersonListRow`, `ClubOption`, person query fields, and all-club response types in `resources/spa/api/types.ts`
- [X] T004 [P] Extend shared listing utilities with pagination-header parsing, trimmed-name validation, debounce behavior, page-reset behavior, and request sequencing helpers in `resources/spa/pages/listingModels.ts` and `resources/spa/pages/listingModels.test.ts`
- [X] T005 [P] Add the shared persons/table/filter/loading/error/empty translation keys required by the feature in `resources/spa/i18n.ts`

**Checkpoint**: Shared SPA contracts and utilities are ready; user-story work can proceed in the
documented dependency order.

---

## Phase 3: User Story 1 - Просмотр и поиск персонов (Priority: P1) 🎯 MVP

**Goal**: Serve `/app/persons` from the Vue SPA with a paginated active-person table, cumulative
name/rank/year/club filtering, cached all-club options, and explicit loading/empty/error states.

**Independent Test**: As a guest, open `/app/persons`, apply each filter separately and in
combination, paginate, verify null-safe rows and empty/error states, and confirm that the API
requests use the documented V1 camelCase contract.

### Tests for User Story 1

> Write these tests first and confirm they fail before implementing the corresponding behavior.

- [X] T006 [P] [US1] Add API regression coverage for partial case-insensitive name search, birth-year filtering, cumulative rank/year/club criteria, validation, deterministic pagination, scalar club/rank fields, auth-only impressions, and no per-row queries in `tests/Feature/Api/V1/Person/ListPersonsActionTest.php`
- [X] T007 [P] [US1] Add separate `/api/v1/clubs/all` contract coverage for the complete active-club `{id,name}` payload, stable ordering, public access, no pagination headers, and unchanged default pagination in `tests/Feature/Api/V1/Club/ListClubsActionTest.php`
- [X] T008 [P] [US1] Add frontend tests for persons query serialization, year-option boundaries, short/blank search handling, pagination reset, debounce, stale-response protection, loading/error/empty states, and filter combinations in `resources/spa/api/persons.test.ts`, `resources/spa/api/clubs.test.ts`, `resources/spa/pages/persons/personsModels.test.ts`, and `resources/spa/pages/persons/PersonsPage.test.ts`

### Implementation for User Story 1

- [X] T009 [US1] Extend request normalization and validation for `name`, `birthYear`, `clubId`, and `rankId` in `app/Application/Dto/Person/SearchPersonDto.php`
- [X] T010 [US1] Map the normalized person search DTO to cumulative `Criteria` values in `app/Application/Service/Person/ListPersons.php`
- [X] T011 [US1] Implement case-insensitive lastname/firstname search, birth-year filtering, active-club filtering, deterministic ordering, and scalar club ids without N+1 queries in `app/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepository.php`
- [X] T012 [US1] Extend the person list projection with `clubId` while preserving auth-only impressions in `app/Application/Dto/Person/ViewPersonDto.php` and `app/Application/Dto/Person/PersonAssembler.php`
- [X] T013 [US1] Implement the separate `/api/v1/clubs/all` endpoint with `ClubOptionDto`, `ListAllClubService`, `ListAllClubAction`, active-only `id/name` projection, stable ordering, no artificial item limit, repository `all()` method, and preserved default pagination in `app/Application/Dto/Club/ClubOptionDto.php`, `app/Application/Service/Club/ListAllClubService.php`, `app/Bridge/Laravel/Http/Controllers/Api/V1/Club/ListAllClubAction.php`, and `app/Infrastructure/Laravel/Eloquent/Club/EloquentClubRepository.php`
- [X] T014 [US1] Update the SPA API adapters to send camelCase person filters and request cached all-club options from `/clubs/all` while leaving paginated `/clubs` uncached in `resources/spa/api/persons.ts` and `resources/spa/api/clubs.ts`
- [X] T015 [US1] Add resilient session caching for active club options and reuse the existing rank-option cache behavior in `resources/spa/api/clubs.ts` and `resources/spa/api/ranks.ts`
- [X] T016 [US1] Implement the reusable null-safe person table with name/detail links, club links, birth year, rank, and injectable action/auth columns in `resources/spa/components/PersonTable.vue`
- [X] T017 [US1] Implement the persons SPA page with filters, current-year birth-year options, pagination, API loading, retryable error, empty state, validation state, debounce cancellation, and monotonically sequenced requests in `resources/spa/pages/persons/PersonsPage.vue` and `resources/spa/pages/persons/personsModels.ts`
- [X] T018 [US1] Register `/app/persons` in the Vue router and make SPA persons navigation point to it in `resources/spa/router/index.ts` and `resources/spa/components/navigationModels.ts`
- [X] T019 [US1] Add persons-page/table styles and labels without changing unrelated legacy translations in `resources/spa/styles.css` and `resources/spa/i18n.ts`
- [X] T020 [US1] Run the focused backend and frontend checks for the complete MVP using `tests/Feature/Api/V1/Person`, `tests/Feature/Api/V1/Club`, `resources/spa/api/persons.test.ts`, `resources/spa/pages/persons/PersonsPage.test.ts`, and `resources/spa/components/PersonTable.test.ts`

**Checkpoint**: US1 is independently demonstrable at `/app/persons` for an anonymous user and
meets the persons API/table/filter acceptance scenarios.

---

## Phase 4: User Story 2 - Переход к действиям персоны (Priority: P2)

**Goal**: Keep existing legacy person actions reachable from the new SPA while preserving current
authorization behavior.

**Independent Test**: As a guest, verify the list and detail links are available but create/edit/
delete actions are hidden; as an authenticated user, verify create/edit/delete links navigate to
the existing legacy routes and legacy forms still load.

### Tests for User Story 2

> Write these tests first and confirm they fail before implementing the corresponding behavior.

- [X] T021 [P] [US2] Add authenticated/anonymous rendering tests for `Дадаць асобу`, detail, edit, and delete links in `resources/spa/pages/persons/PersonsPage.test.ts` and `resources/spa/components/PersonTable.test.ts`
- [X] T022 [P] [US2] Add regression coverage for existing person detail/create/edit authorization and responses in `tests/Bridge/Laravel/Http/Controllers/Person/ShowPersonActionTest.php`, `tests/Bridge/Laravel/Http/Controllers/Person/ShowCreatePersonActionTest.php`, and `tests/Bridge/Laravel/Http/Controllers/Person/ShowEditPersonActionTest.php`

### Implementation for User Story 2

- [X] T023 [US2] Wire the authenticated create action and existing legacy detail/edit/delete hrefs through the persons page and shared table in `resources/spa/pages/persons/PersonsPage.vue` and `resources/spa/components/PersonTable.vue`
- [X] T024 [US2] Load authenticated user impression data only when required by the shared table and preserve guest-safe rendering in `resources/spa/pages/persons/PersonsPage.vue`, `resources/spa/components/PersonTable.vue`, and `resources/spa/api/users.ts`
- [X] T025 [US2] Update SPA navigation tests and labels to verify the persons entry remains `/app/persons` while row actions remain full-page legacy hrefs in `resources/spa/components/AppLayout.test.ts`, `resources/spa/router/index.test.ts`, and `resources/spa/i18n.ts`
- [X] T026 [US2] Run the focused US2 frontend and legacy person-controller tests using `resources/spa/pages/persons/PersonsPage.test.ts`, `resources/spa/components/PersonTable.test.ts`, and `tests/Bridge/Laravel/Http/Controllers/Person`

**Checkpoint**: US1 and US2 both work; guests can browse, authenticated users retain existing
person operations, and no detail/form route has been replaced by SPA code.

---

## Phase 5: User Story 3 - Единая таблица и удаление легаси списка (Priority: P2)

**Goal**: Reuse one table on persons and club details, migrate internal list consumers to a target
Application criteria use case, and remove the old person-list routes, mount, and list-only classes.

**Independent Test**: Verify both SPA pages render the same person-table structure, `/persons` and
`/api/person*` return 404, zero production references remain to the removed list boundary, internal
event/cup/console scenarios still render, and legacy detail/create/edit routes remain reachable.

### Tests for User Story 3

> Write these tests first and confirm they fail before implementing the corresponding behavior.

- [X] T027 [P] [US3] Add a shared-table regression test proving `ClubDetailsPage` renders `PersonTable` with identical columns, null handling, pagination and legacy links in `resources/spa/pages/clubs/ClubDetailsPage.test.ts` and `resources/spa/components/PersonTable.test.ts`
- [X] T028 [P] [US3] Add route-removal regression tests for HTTP 404 at `/persons`, `/api/person`, and `/api/persons`, plus preservation tests for `/persons/{id}/show`, `/persons/create`, and `/persons/{id}/edit` in `tests/Bridge/Laravel/Http/Controllers/Person/LegacyPersonListRouteTest.php` and `tests/Bridge/Laravel/Http/Controllers/Api/RemovedPersonListRoutesTest.php`
- [X] T029 [P] [US3] Add Application/use-case and consumer regression coverage for ids, year, and without-lines/payments criteria without creating Eloquent entities in `tests/Application/Service/Person/FindPersonsServiceTest.php`, `tests/Bridge/Laravel/Http/Controllers/Event/ShowEventActionTest.php`, `tests/Bridge/Laravel/Http/Controllers/Cup/ShowCupTableActionTest.php`, and `tests/Bridge/Laravel/Console/Commands/FixYearCommandTest.php`

### Implementation for User Story 3

- [X] T030 [US3] Add a target non-paginated person lookup command/service accepting `Criteria` and returning the existing view projection required by legacy Blade consumers in `app/Application/Service/Person/FindPersons.php`, `app/Application/Service/Person/FindPersonsService.php`, and `tests/Application/Service/Person/FindPersonsServiceTest.php`
- [X] T031 [US3] Migrate event rendering and distance actions from `ListLegacyPersonsService`/`LegacySearchPersonDto` to the target criteria use case while preserving one upfront person query in `app/Bridge/Laravel/Http/Controllers/Event/RendersEventDistance.php`, `app/Bridge/Laravel/Http/Controllers/Event/ShowEventAction.php`, and `app/Bridge/Laravel/Http/Controllers/Event/ShowEventDistanceAction.php`
- [X] T032 [US3] Migrate cup table person loading from the removed legacy list service to the target criteria use case while preserving person/club maps and Blade payloads in `app/Bridge/Laravel/Http/Controllers/Cup/ShowCupTableAction.php`
- [X] T033 [US3] Migrate inactive-person pruning to the target criteria use case and year-fix to the existing paginated person list service/Slice in `app/Bridge/Laravel/Console/Commands/PruneInactivePersonsCommand.php` and `app/Bridge/Laravel/Console/Commands/FixYearCommand.php`
- [X] T034 [US3] Replace the duplicated club-details person markup with `PersonTable`, passing the existing person pagination, auth state, and user data in `resources/spa/pages/clubs/ClubDetailsPage.vue`
- [X] T035 [US3] Remove the non-versioned person list API controllers, collection/resource, route provider registration, and their obsolete tests while retaining `/api/v1/persons` in `app/Bridge/Laravel/Http/Controllers/Api/ListPersonAction.php`, `app/Bridge/Laravel/Http/Controllers/Api/PersonController.php`, `app/Bridge/Laravel/Http/Resources/PersonCollection.php`, `app/Bridge/Laravel/Provider/ApiRoutesServiceProvider.php`, `config/app.php`, `tests/Bridge/Laravel/Http/Controllers/Api/ListPersonActionTest.php`, and `tests/Bridge/Laravel/Http/Controllers/Api/PersonControllerTest.php`
- [X] T036 [US3] Remove the list-only person application DTO/command/service and their test after all consumers are migrated in `app/Application/Dto/Person/LegacySearchPersonDto.php`, `app/Application/Service/Person/ListLegacyPersons.php`, `app/Application/Service/Person/ListLegacyPersonsService.php`, and `tests/Application/Service/Person/ListLegacyPersonsServiceTest.php`
- [X] T037 [US3] Remove the Laravel `/persons` list route, list action, Blade mount, Vue 2 component, and its component registration while preserving person detail/form views in `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, `app/Bridge/Laravel/Http/Controllers/Person/ShowPersonsListAction.php`, `resources/views/persons/index.blade.php`, `resources/vue/components/person/Persons.vue`, and `resources/js/app.js`
- [X] T038 [US3] Update Blade navbar links and missing-person/delete/payment redirects to `/app/persons` without changing legacy detail/action routes in `resources/views/layouts/navbar.blade.php`, `app/Bridge/Laravel/Http/Controllers/Person/ShowPersonAction.php`, `app/Bridge/Laravel/Http/Controllers/Person/DeletePersonAction.php`, and `app/Bridge/Laravel/Http/Controllers/PersonPayment/ShowPersonPaymentsListAction.php`
- [X] T039 [US3] Remove only translations/assets proven unused by the deleted Vue 2 list and verify no production reference remains to `ShowPersonsListAction`, `LegacySearchPersonDto`, `ListLegacyPersonsService`, `/api/person`, `/api/persons`, or the old component in `resources/lang`, `resources/spa`, `resources/vue`, `resources/js`, `app`, and `resources/views`
- [X] T040 [US3] Run focused US3 backend/frontend checks and the quickstart route/reference checks using `tests/Application/Service/Person`, `tests/Bridge/Laravel/Http/Controllers/Event`, `tests/Bridge/Laravel/Http/Controllers/Cup`, `tests/Bridge/Laravel/Console/Commands`, `resources/spa/pages/clubs/ClubDetailsPage.test.ts`, and `specs/012-persons-spa/quickstart.md`

**Checkpoint**: All three user stories are complete; the new SPA is the only person-list UI/API
boundary and legacy detail/form/action flows remain available.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Validate the complete feature against the specification, contracts, constitution and
runtime boundaries.

- [X] T041 [P] Run `npm run lint`, `npm run typecheck`, focused Vitest tests, and `npm run build:spa` using `package.json`, `resources/spa`, and `public/spa`
- [X] T042 [P] Run backend feature/application/bridge tests and inspect query counts for the persons and club-option paths using `composer.json`, `tests/Feature/Api/V1/Person`, `tests/Feature/Api/V1/Club`, and `tests/Application/Service/Person`
- [X] T043 Run the final Definition of Done gates `composer test`, `npm run ci`, `composer cs -- --sequential`, `composer stan`, `composer rector -- --dry-run`, `git diff --check`, and `php artisan route:list` with acceptance evidence recorded in `specs/012-persons-spa/quickstart.md`
- [X] T044 Verify every acceptance scenario, contract field, route-preservation rule, and checklist item against the implementation in `specs/012-persons-spa/spec.md`, `specs/012-persons-spa/contracts/`, `specs/012-persons-spa/checklists/requirements.md`, and `specs/012-persons-spa/quickstart.md`

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 Setup**: No implementation dependency; verify the existing SPA/build baseline first.
- **Phase 2 Foundational**: Depends on Setup; blocks all user-story implementation.
- **Phase 3 US1**: Depends on Phase 2 and delivers the MVP. US1 owns the first person row contract,
  shared table, persons API extensions and persons page.
- **Phase 4 US2**: Depends on US1 because it adds actions to `PersonsPage`/`PersonTable`; it does
  not replace or redesign the legacy action routes.
- **Phase 5 US3**: Depends on US1's shared table and US2's legacy-link coverage; migrations must
  complete before deleting legacy DTO/service/routes.
- **Phase 6 Polish**: Depends on all required stories and their focused checks.

### User Story Dependencies

```text
Phase 1 → Phase 2 → US1 (P1) → US2 (P2)
                         └────→ US3 (P2) → Phase 6
```

US2 and the first half of US3 can be developed in parallel after US1 if separate developers avoid
editing the same SPA files. The final US3 cleanup depends on all consumer migration tests passing.

### Within Each User Story

- Write and run the story tests first; keep them failing until implementation tasks are complete.
- Complete backend contracts/use cases before the API/UI consumers that depend on them.
- Complete shared `PersonTable` before changing `ClubDetailsPage` to consume it.
- Migrate every internal consumer before deleting `LegacySearchPersonDto` or
  `ListLegacyPersonsService`.
- Run the story checkpoint before starting the next dependent story.

## Parallel Opportunities

- **Setup/Foundational**: T002, T003, T004 and T005 touch separate test/contract/helper files and
  can run in parallel after T001.
- **US1 tests**: T006, T007 and T008 can run in parallel. After them, T009–T012 (person backend)
  and T013 (club options) can be split by backend owner; T014–T015 are then frontend API work.
- **US2**: T021 and T022 can run in parallel; implementation must wait for US1 page/table files.
- **US3 tests**: T027, T028 and T029 can run in parallel. T031, T032 and T033 can run in parallel
  after T030, because they migrate separate event, cup and console consumers.
- **Final validation**: T041 and T042 can run in parallel; T043/T044 follow their results.

## Parallel Example: User Story 1

```text
T006: Backend persons API regression tests in tests/Feature/Api/V1/Person/ListPersonsActionTest.php
T007: All-club API regression tests in tests/Feature/Api/V1/Club/ListClubsActionTest.php
T008: SPA query/page tests in resources/spa/api/persons.test.ts and resources/spa/pages/persons/
```

## Parallel Example: User Story 2

```text
T021: Auth visibility and href tests in resources/spa/pages/persons/ and resources/spa/components/
T022: Legacy detail/create/edit regression tests in tests/Bridge/Laravel/Http/Controllers/Person/
```

## Parallel Example: User Story 3

```text
T027: Shared table/club-details tests in resources/spa/pages/clubs/ and resources/spa/components/
T028: Removed-route tests in tests/Bridge/Laravel/Http/Controllers/{Api,Person}/
T029: Internal consumer/use-case tests in tests/Application/Service/Person/ and tests/Bridge/Laravel/
```

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 and the blocking Phase 2.
2. Write and implement US1 API, all-club options, shared table and `/app/persons` page.
3. Run T020 and the US1 acceptance scenarios in `specs/012-persons-spa/quickstart.md`.
4. Stop at the US1 checkpoint for an independently demonstrable guest-facing MVP.

### Incremental Delivery

1. Add US2 legacy create/detail/edit/delete visibility and verify existing routes.
2. Add US3 club-details reuse and migrate event/cup/console consumers.
3. Delete old list routes/classes/assets only after T027–T029 regressions pass.
4. Run Phase 6 once at feature completion and record evidence against the specification checklist.

### Notes

- `[P]` means the task touches files that can be changed independently after its listed
  prerequisites; it is not a blanket permission to edit shared files concurrently.
- `[US1]`, `[US2]`, and `[US3]` map directly to the prioritized stories in `spec.md`.
- No database migration or new Composer/NPM dependency is planned.
- Preserve unrelated user changes and do not remove `LegacyViewPersonDto` or legacy detail/form
  functionality merely because the list boundary is being deleted.
