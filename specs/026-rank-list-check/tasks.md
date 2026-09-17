---

description: "Task list template for feature implementation"
---

# Tasks: Асинхронная проверка разрядов по списку

**Input**: Design documents from `/specs/026-rank-list-check/`

**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/](contracts/)

**Tests**: Required by the specification and project constitution; write tests first and keep application/domain tests free of Eloquent entities.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare feature paths and fixtures without adding dependencies.

- [ ] T001 Add the six-column and five-column list fixtures, including matching, mismatched, missing-person, blank and vacancy rows, in `tests/fixtures/rank-check/`
- [ ] T002 [P] Add fixture expectations for the historical seven-column result in `tests/fixtures/rank-check/README.md`

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Establish the domain lifecycle, persistence ports and storage schema required by every user story.

- [x] T003 [P] Define `RankCheckStatus` with only `PARSING`, `READY` and `FAILED` in `app/Domain/RankCheck/RankCheckStatus.php`
- [x] T004 [P] Define the `RankCheck` domain aggregate and guarded final-state transitions in `app/Domain/RankCheck/RankCheck.php`
- [x] T005 [P] Define `RankCheckRow` domain input/result model in `app/Domain/RankCheck/RankCheckRow.php`
- [x] T006 Define `RankCheckRepository` query/add/update port and application ports for list parsing, person identification, current-rank lookup and source-file storage in `app/Domain/RankCheck/RankCheckRepository.php` and `app/Application/Port/RankCheck/`
- [ ] T007 [P] Define `RankCheckResources` and read models for status/result loading in `app/Domain/RankCheck/RankCheckResources.php` and `app/Domain/RankCheck/RankCheckInfo.php`
- [x] T008 Create the `rank_checks` and `rank_check_rows` migrations with indexes, foreign keys, status constraints and source-path/error fields in `database/migrations/`
- [x] T009 [P] Implement Eloquent persistence models and repository adapter with row ordering, server-pagination query and eager-loading rules in `app/Infrastructure/Laravel/Eloquent/RankCheck/`
- [x] T010 [P] Implement Infrastructure adapters for the preserved `createListParser`, existing person-identification flow, current-rank lookup and private source-file storage in `app/Infrastructure/RankCheck/`
- [ ] T011 [P] Add domain tests for allowed transitions, final-state immutability, `updatedAt` changes and row-position uniqueness in `tests/Domain/RankCheck/RankCheckTest.php`
- [ ] T012 Add integration tests for schema persistence, status/`updatedAt` updates, ordered rows and paginated row queries in `tests/Infrastructure/Laravel/Eloquent/RankCheck/EloquentRankCheckRepositoryTest.php`

**Checkpoint**: Domain lifecycle and durable read model are ready; no story can expose partial or cross-user results.

## Phase 3: User Story 1 - Запустить проверку списка (Priority: P1) 🎯 MVP

**Goal**: An administrator can access the navigation item, upload a supported list and receive a `202` launch response with `PARSING`.

**Independent Test**: API request tests authenticate an administrator, upload a fixture and assert the launch resource without waiting for the worker.

### Tests for User Story 1

- [x] T013 [P] [US1] Add application unit tests for create command validation, source storage, domain event and immediate return without waiting for event handler in `tests/Application/Service/RankCheck/CreateRankCheckServiceTest.php`
- [ ] T014 [P] [US1] Add API request tests for `202`, `401`, empty file, unsupported file and malformed list in `tests/Feature/Api/V1/RankCheck/CreateRankCheckActionTest.php`
- [ ] T015 [P] [US1] Add SPA tests for authenticated-only navigation, upload validation, disabled submit and redirect to a new check in `resources/spa/pages/rank-checks/RankCheckUploadPage.test.ts`

### Implementation for User Story 1

- [x] T016 [US1] Implement create command and application service that stores the source, creates a `PARSING` aggregate and publishes its domain event without waiting for the queued handler in `app/Application/Service/RankCheck/CreateRankCheck.php` and `app/Application/Service/RankCheck/CreateRankCheckService.php`
- [x] T017 [US1] Implement authenticated API action and `202` resource mapping in `app/Bridge/Laravel/Http/Controllers/Api/V1/RankCheck/CreateRankCheckAction.php`
- [x] T018 [US1] Register protected `POST /api/v1/rank-checks` and `GET /api/v1/rank-checks/{rankCheckId}` routes in `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [x] T019 [US1] Implement the queued `RankCheckCreated` event handler that starts processing by aggregate ID in `app/Application/Handler/RankCheck/RankCheckCreatedHandler.php`
- [x] T020 [US1] Add authenticated navigation entry and rank-check upload route/page in `resources/spa/router.ts`, `resources/spa/components/Navigation.vue` and `resources/spa/pages/rank-checks/RankCheckUploadPage.vue`
- [x] T021 [US1] Add API client types and multipart create method in `resources/spa/api/rankChecks.ts` and `resources/spa/api/types.ts`

**Checkpoint**: Upload creates an isolated pending check and never performs the full pipeline inside the HTTP request.

## Phase 4: User Story 2 - Видеть ход и завершение обработки (Priority: P1)

**Goal**: The check page displays pending state, polls its own resource and switches to the saved result after `READY`.

**Independent Test**: Component tests mock status responses and verify polling, terminal stop and unmount cleanup.

### Tests for User Story 2

- [x] T022 [P] [US2] Add domain/processor and application tests for successful job orchestration, atomic `PARSING` to `READY` transition, `updatedAt` update and no HTTP wait for the job in `tests/Domain/RankCheck/StandardRankCheckProcessorTest.php`
- [ ] T023 [P] [US2] Add API request tests for `PARSING`, `READY`, `FAILED`, `updatedAt` and authenticated access in `tests/Feature/Api/V1/RankCheck/ViewRankCheckActionTest.php`
- [ ] T024 [P] [US2] Add SPA tests for pending polling every 5 seconds, ready stop, route-change cancellation, 5-second status visibility and network retry warning in `resources/spa/pages/rank-checks/RankCheckViewPage.test.ts`

### Implementation for User Story 2

- [x] T025 [US2] Implement status/result query and application read service in `app/Application/Service/RankCheck/ViewRankCheck.php` and `app/Application/Service/RankCheck/ViewRankCheckService.php`
- [x] T026 [US2] Implement API view action with status-only pending payload and complete ready payload in `app/Bridge/Laravel/Http/Controllers/Api/V1/RankCheck/ViewRankCheckAction.php`
- [x] T027 [US2] Implement queue processing orchestration, transaction boundary and terminal failure handling in `app/Application/Service/RankCheck/ProcessRankCheck.php` and `app/Application/Service/RankCheck/ProcessRankCheckService.php`
- [x] T028 [US2] Complete queued event-handler error reporting and `FAILED` transition without exposing source content in `app/Application/Handler/RankCheck/RankCheckCreatedHandler.php`
- [x] T029 [US2] Implement rank-check view page with pending/ready states, bounded polling and unmount cancellation in `resources/spa/pages/rank-checks/RankCheckViewPage.vue`
- [x] T030 [US2] Extend `resources/spa/api/rankChecks.ts` with typed view polling, pagination and error handling

**Checkpoint**: A delayed worker is observable from the SPA and terminal states stop all polling.

## Phase 5: User Story 3 - Разобрать расхождения с базой (Priority: P1)

**Goal**: A ready check reproduces the historical parser pipeline and displays every accepted input row with source/database comparison values.

**Independent Test**: Run the worker against fixtures and assert rows for match, mismatch, missing person, five/six-column input and skipped rows.

### Tests for User Story 3

- [x] T031 [P] [US3] Add regression tests for `createListParser` six-field/five-field mapping, header skipping, vacancy/blank skipping and preserved order in `tests/Models/Parser/CsvListParserTest.php`
- [x] T032 [P] [US3] Add domain unit tests for historical pipeline mapping, rank normalization, missing current rank and source/database equality flags in `tests/Domain/RankCheck/Factory/StandardRankCheckRowFactoryTest.php` and `tests/Domain/RankCheck/StandardRankCheckProcessorTest.php`
- [ ] T033 [P] [US3] Add API response tests for server pagination, the seven historical columns, source/database values and ordered duplicate rows in `tests/Feature/Api/V1/RankCheck/RankCheckRowsContractTest.php`
- [ ] T034 [P] [US3] Add SPA tests for match, mismatch, missing-person display, server pagination and page navigation in `resources/spa/pages/rank-checks/RankCheckViewPage.test.ts`

### Implementation for User Story 3

- [x] T035 [US3] Implement the domain-level row processor against parser, person-matching, snapshot and row-factory ports in `app/Domain/RankCheck/StandardRankCheckProcessor.php`
- [x] T036 [US3] Persist source and database snapshots plus `hasPerson`/`isEqual` flags in the processing service and Eloquent adapter in `app/Application/Service/RankCheck/ProcessRankCheckService.php` and `app/Infrastructure/Laravel/Eloquent/RankCheck/`
- [ ] T037 [US3] Add the seven-column comparison table, explicit source-to-database differences and person/rank links in `resources/spa/pages/rank-checks/RankCheckResultTable.vue`
- [x] T038 [US3] Add localized labels for navigation, statuses, columns, match states and errors in `resources/lang/ru.json` and `resources/lang/by.json`

**Checkpoint**: `READY` is a durable, ordered snapshot with the same meaningful output as the removed legacy screen.

## Phase 6: User Story 4 - Понять ошибку обработки (Priority: P2)

**Goal**: Invalid input and worker failures produce `FAILED`, a safe localized message and no partial result.

**Independent Test**: Force parser and worker failures and verify terminal error rendering, logging and stopped polling.

### Tests for User Story 4

- [ ] T039 [P] [US4] Add failure-transition and no-partial-rows tests in `tests/Application/Service/RankCheck/ProcessRankCheckFailureTest.php`
- [ ] T040 [P] [US4] Add API tests for safe error payload and no source-data leakage in `tests/Feature/Api/V1/RankCheck/FailedRankCheckActionTest.php`
- [ ] T041 [P] [US4] Add SPA tests for `FAILED`, safe message, no result table and terminal polling stop in `resources/spa/pages/rank-checks/RankCheckViewPage.test.ts`

### Implementation for User Story 4

- [x] T042 [US4] Add explicit parser/input validation and safe application exceptions in `app/Application/Service/RankCheck/CreateRankCheckService.php` and `app/Application/Service/RankCheck/ProcessRankCheckService.php`
- [x] T043 [US4] Map failures to localized API/UI-safe errors while retaining diagnostic exception context for centralized error tracking in `app/Bridge/Laravel/Http/Controllers/Api/V1/RankCheck/` and the queued domain-event handler
- [x] T044 [US4] Finalize failure UI and non-terminal network retry warning in `resources/spa/pages/rank-checks/RankCheckViewPage.vue`

**Checkpoint**: Every accepted check ends in `READY` or `FAILED`; no run remains pending forever or exposes partial rows.

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Verify security, performance, docs and project gates.

- [x] T045 [P] Add authenticated-only access and 24-hour retention/security review notes to `specs/026-rank-list-check/quickstart.md`
- [ ] T046 [P] Add queue idempotency, concurrent-read, paginated-read and no-new-N+1 integration coverage in `tests/Feature/Api/V1/RankCheck/RankCheckConcurrencyTest.php`
- [x] T047 Add daily cleanup command/schedule for completed `RankCheck`, related `RankCheckRow` records and source files older than 24 hours in `app/Bridge/Laravel/Console/Commands/CleanupRankChecksCommand.php` and `app/Bridge/Laravel/Console/Kernel.php`
- [ ] T048 [P] Add cleanup retention tests that preserve active/newer runs in `tests/Feature/RankCheck/CleanupRankChecksCommandTest.php`
- [x] T049 Run the focused PHP and SPA test commands from `specs/026-rank-list-check/quickstart.md`
- [x] T050 Run final `composer test`, `composer stan`, `composer cs`, Rector dry-run, frontend lint/typecheck/test/build and `git diff --check`
- [x] T051 Compare implementation against `spec.md`, `data-model.md`, contracts and checklist; update completion markers in `specs/026-rank-list-check/checklists/requirements.md`

## Dependencies & Execution Order

### Phase Dependencies

- Setup (Phase 1) has no dependencies.
- Foundational (Phase 2) depends on setup and blocks all stories.
- User Story 1 depends on the domain lifecycle and persistence foundation.
- User Story 2 depends on User Story 1's launch contract and shared domain foundation.
- User Story 3 depends on User Story 1's job boundary and User Story 2's durable status/read contract.
- User Story 4 depends on User Story 2's terminal failure/read contract and can be hardened alongside User Story 3.
- Polish depends on all desired stories.

### Parallel Opportunities

- T003–T005, T007, T009–T011 can be developed in parallel after fixture setup.
- T013–T015 are parallel red tests for User Story 1; T016–T021 then follow the API/domain dependencies.
- T022–T024 are parallel red tests for User Story 2.
- T031–T034 are parallel regression/contract/UI tests for User Story 3.
- T040–T042 are parallel failure tests for User Story 4.
- T046–T047 and T049 can run in parallel with final story integration; T048 depends on the retention model.

### Within Each User Story

Tests are written first and verified red, then domain/application code, adapters/actions, and finally SPA integration. A story is complete only when its independent test criteria pass.

## Implementation Strategy

### MVP First

1. Complete setup and foundational lifecycle/persistence.
2. Complete User Story 1: protected upload creates a pending run.
3. Complete User Story 2: pending polling reaches a durable ready result.
4. Complete the minimum User Story 3 pipeline and historical seven-column result.
5. Validate this MVP before adding extended failure hardening and final gates.

### Incremental Delivery

1. Launch contract and status page can be demonstrated with a controlled worker.
2. Add real historical pipeline and comparison snapshot.
3. Add failure and security hardening.
4. Run all final project gates once at feature completion.
