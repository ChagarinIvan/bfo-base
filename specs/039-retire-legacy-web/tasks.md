# Tasks: Retire Legacy Web Routes and Services

**Input**: [spec.md](spec.md), [plan.md](plan.md), [research.md](research.md), [data-model.md](data-model.md), [routes-and-export.md](contracts/routes-and-export.md)

## Phase 1: Inventory and regression baseline

- [X] T001 [US1][US2] Record live route and caller inventory in `specs/039-retire-legacy-web/legacy-inventory.md`; identify old frontend files, email exceptions, and all `app/Services` callers.
- [X] T002 [US1] Add API request tests for cup disable, stage disable, and cache clear in `tests/Feature/Api/V1/Cup/`; cover auth, missing resources, mutation, and cache invalidation (FR-001, FR-002).
- [X] T003 [US2] Add full export API request tests in `tests/Feature/Api/V1/Cup/`; cover CSV quoting, empty data, auth, errors, agreement with table view, and absence of the retired group export route (FR-003, FR-004, FR-008).

## Phase 2: Cup API and SPA

- [X] T004 [US2] Add export commands and Application services in `app/Application/Service/Cup/` using `CupRepository`, `CupEventRepository`, and `CupTableBuilder`; add focused mocked unit coverage (FR-003, FR-008).
- [X] T005 [US2] Add CSV serializer and V1 full export action in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/`; register the full export route in `ApiV1RoutesServiceProvider.php` and remove the old export controllers and `CupEventsService.php` (FR-003, FR-004, FR-008).
- [X] T006 [US1] Add V1 delete and cache-clear actions in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/`, reusing existing Application commands/services; register routes with `AuthenticateApiV1` (FR-001, FR-002).
- [X] T007 [US1][US2] Add SPA API client calls and replace old cup action links in `resources/spa/api/cups.ts`, `resources/spa/pages/cups/`, and `resources/spa/components/`; add focused Vitest coverage (FR-005).

## Phase 3: Distance service removal

- [X] T008 [US4] Add focused regression coverage for equal/group distance selection and event cleanup using repository mocks for unit tests and real records only in integration tests (FR-009, FR-011).
- [X] T009 [US4] Share `DistanceRepository` criteria through protected `AbstractCupType` methods and add a `DistanceDeleter` port with Infrastructure implementation; bind ports in `DistanceProvider.php` (FR-009).
- [X] T010 [US4] Replace `DistanceService` in cup types and event handlers with the direct repository-backed base methods and deletion port; remove `app/Services/DistanceService.php` (FR-009).

## Phase 4: Retire old web layer

- [X] T011 [US3] Move root redirect into `ApiV1RoutesServiceProvider`, remove `WebRoutesServiceProvider` from `config/app.php`, and delete it; assert old routes are absent (FR-001, FR-006).
- [X] T012 [US3] Remove obsolete browser controllers, action base, view helper services, Blade layouts/components, Mix sources/assets/dependencies, and their registration provider; retain mail views and verify mail rendering (FR-007).
- [X] T013 [US4] Finish `legacy-inventory.md` with caller-backed disposition of every remaining `app/Services` class; remove additional unreferenced class only when no larger refactor is required (FR-010).

## Phase 5: Verification

- [X] T014 [US1][US2][US3][US4] Run focused changed-behavior tests, inspect route list and old-route side effects, check full export cache reuse, and compare acceptance scenarios with contracts (FR-011, SC-001 through SC-004).
- [X] T015 [US1][US2][US3][US4] Run final full PHPUnit suite, `composer stan`, `composer cs`, Rector dry-run, frontend CI, and local application startup; address feature regressions (SC-005). `composer test` hit its 300-second process timeout; direct PHPUnit completed the same suite in 3:56.

## Dependencies and execution order

- T001 precedes deletions and T013.
- T002 and T003 precede their corresponding implementation tasks.
- T004 precedes T005; T005 and T006 precede T007 and T011.
- T008 precedes T009 and T010; T009 precedes T010.
- T011 and T012 follow completed API and SPA replacement.
- T014 and T015 follow all implementation tasks; the final full gates run once.

No task is marked parallel because route, export, and SPA files have shared dependencies and this implementation uses one working tree.
