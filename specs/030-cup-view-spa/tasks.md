# Tasks: Cup View SPA

**Input**: Design documents from `/specs/030-cup-view-spa/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Required by FR-012 and the project constitution.

## Phase 1: Setup

- [X] T001 Record the legacy cup-show behaviour, retained action targets, and current dirty-worktree state in `specs/030-cup-view-spa/research.md`
- [X] T002 [P] Add Belarusian cup-view, stage-table, filter, and confirmation strings in `resources/lang/by.json`

## Phase 2: Foundational API pagination

- [X] T003 Add date/name query validation and criteria mapping to `app/Application/Dto/CupEvent/CupEventSearchDto.php` and `app/Application/Service/CupEvent/ListCupEvent.php`
- [X] T004 Add `Slice` pagination to `app/Domain/Cup/CupEvent/CupEventRepository.php`, `app/Infrastructure/Laravel/Eloquent/CupEvent/EloquentCupEventRepository.php`, and `app/Application/Service/CupEvent/ListCupEventService.php`
- [X] T005 Add authenticated serialization groups to impressions in `app/Application/Dto/CupEvent/ViewCupEventDto.php`
- [X] T006 Add `ListCupEventsAction` and its optional-auth V1 route; move `ViewCupAction` to optional authentication in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/` and `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [ ] T007 [P] Add Application tests for cup-stage criteria, Slice mapping, and bounded repository calls in `tests/Application/Service/CupEvent/ListCupEventServiceTest.php`
- [X] T008 [P] Add request tests with class-level `@see` for cup detail and cup-event pagination, filters, 404, authenticated impressions, and public isolation in `tests/Feature/Api/V1/Cup/ListCupEventsActionTest.php`

## Phase 3: User Story 1 - View a cup and its stages (Priority: P1)

**Goal**: A guest can use an SPA card and paginated, filtered stage table.

**Independent Test**: Open `/app/cups/:cupId`, verify default 50-row request, name/date filtering, pagination, stage links, empty state, and not-found routing.

- [X] T009 [P] [US1] Add `CupEvent`, `CupEventSearchQuery`, and paginated `getCupEvents` API client coverage in `resources/spa/api/{types,cups}.{ts,test.ts}`
- [X] T010 [P] [US1] Add query normalization/reset tests and helpers in `resources/spa/pages/cups/{cupViewModels,cupViewModels.test}.ts`
- [X] T011 [US1] Write failing card/list/filter/pagination/not-found page tests in `resources/spa/pages/cups/CupViewPage.test.ts`
- [X] T012 [US1] Implement `CupViewPage` with shared Card, ListingTable, FilterPanel, date/name filters, and 50-row pagination in `resources/spa/pages/cups/CupViewPage.vue`
- [X] T013 [US1] Register `/app/cups/:cupId` and replace cup-list detail links in `resources/spa/router/{index,index.test}.ts` and `resources/spa/pages/cups/{CupsPage,CupsPage.test}.vue`

## Phase 4: User Story 2 - Use authenticated cup controls (Priority: P2)

**Goal**: Administrators retain audit and legacy workflow controls; guests cannot see them.

**Independent Test**: Compare guest and authenticated page renderings and verify card/row action destinations and confirmations.

- [X] T014 [US2] Extend `CupViewPage` tests with guest/authenticated impressions, card controls, stage edit/delete controls, and confirmation cases in `resources/spa/pages/cups/CupViewPage.test.ts`
- [X] T015 [US2] Add authenticated-only impressions, retained card actions, stage actions, and confirmation dialogs in `resources/spa/pages/cups/CupViewPage.vue`
- [X] T016 [US2] Update legacy workflow redirects that returned to cup show in `app/Bridge/Laravel/Http/Controllers/{Cup,CupEvents}/` and their focused tests in `tests/Bridge/Laravel/Http/Controllers/{Cup,CupEvents}/`

## Phase 5: User Story 3 - Retire the legacy cup-show page (Priority: P3)

**Goal**: The SPA is the sole cup-detail page and obsolete Blade surface is gone.

**Independent Test**: Retired show URL is absent; retained cup routes and redirects work.

- [X] T017 [US3] Add failing route-retirement and retained-route regression coverage in `tests/Feature/Cup/CupViewRetirementTest.php`
- [X] T018 [US3] Remove `ShowCupAction`, its web route/import, `resources/views/cup/show.blade.php`, and uniquely obsolete controller tests in `app/Bridge/Laravel/Http/Controllers/Cup/`, `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, `resources/views/cup/`, and `tests/Bridge/Laravel/Http/Controllers/Cup/`

## Phase 6: Polish and verification

- [ ] T019 Run focused PHPUnit and Vitest suites for cup detail, cup events, routes, API client, router, and page files
- [ ] T020 Run `composer cs`, `composer rector -- --dry-run`, `XDEBUG_MODE=off composer stan --memory-limit=4G`, `composer test`, `npm run ci`, and `git diff --check`; inspect eager loading to confirm no new N+1 or unbounded stage read
- [ ] T021 Validate `specs/030-cup-view-spa/quickstart.md`, mark all tasks complete, and reconcile results against spec/plan/contracts

## Dependencies & Execution Order

- T001–T008 establish the bounded V1 read contract and block SPA work.
- T009–T013 implement the public MVP.
- T014–T016 add authenticated controls after the public page exists.
- T017–T018 retire Blade only after SPA routes and redirects are verified.
- T019–T021 finish after all stories.

## Parallel Opportunities

- T002, T007, and T008 are parallelizable after their prerequisites.
- T009 and T010 can proceed in parallel after the API contract is set.
- API and SPA focused tests can be run concurrently once their code paths exist.

## Implementation Strategy

1. Convert the existing cup-event query to a bounded `Slice` and expose it through V1.
2. Deliver the public card/table/filter/pagination flow first.
3. Restore staff-only audit and action controls using retained endpoints.
4. Delete only the replaced Blade render surface and validate retained workflows.
