# Tasks: Count-Free Slice Pagination

**Input**: Design documents from `/specs/027-count-free-slice-pagination/`

**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md), [data-model.md](data-model.md), [contracts/](contracts/), [quickstart.md](quickstart.md)

**Tests**: Required by the feature specification. Write focused tests before implementation where the task is marked as a test task.

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Establish the inventory and baseline needed to implement and measure the shared Slice safely.

- [X] T001 Build the paginated listing inventory with endpoint, Application service, repository adapter, filters, ordering, relations and existing tests in `specs/027-count-free-slice-pagination/listing-matrix.md`
- [ ] T002 Capture baseline latency, SQL query count and explain-plan notes for the heavy listings in `specs/027-count-free-slice-pagination/query-profiles.md`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Prepare the shared backend and transport seams that every user story depends on.

**⚠️ CRITICAL**: No user story implementation can begin until these shared contracts are settled.

- [X] T003 Define the count-free Slice read contract and remove total/last-page concepts from the shared design in `app/Domain/Shared/Pagination/Slice.php`
- [X] T004 Define the bounded page-read adapter seam without a `getNbResults()` requirement in `app/Infrastructure/Laravel/Eloquent/Pagination/EloquentQueryAdapter.php`
- [X] T005 Update shared API pagination header handling to emit only Current-Page, Per-Page and Has-Next in `app/Bridge/Laravel/Http/Controllers/ApiAction.php`
- [X] T006 Update the shared pagination type and header parser contract for `hasNext` in `resources/spa/api/types.ts` and `resources/spa/pages/listingModels.ts`

**Checkpoint**: Shared count-free contract is ready; user story work can proceed in priority order or in parallel where files do not overlap.

---

## Phase 3: User Story 1 - Просмотр списка без подсчёта общего количества (Priority: P1) 🎯 MVP

**Goal**: Every shared Slice page reads `perPage + 1`, trims the probe row, exposes `hasNext`, and never executes a count query.

**Independent Test**: The shared Slice regression test proves with `perPage=20` that one bounded read requests 21 rows, returns 20, sets `hasNext=true`, and makes no count call; empty and exact-page-size cases set `hasNext=false`.

### Tests for User Story 1

- [X] T007 [P] [US1] Add the single mandatory no-COUNT Slice regression test for the 21-row probe, trimmed output and `hasNext` in `tests/Domain/Shared/Pagination/SliceTest.php`
- [X] T008 [P] [US1] Add adapter-level coverage that asserts the requested offset and exact `perPage + 1` limit without invoking a count in `tests/Infrastructure/Laravel/Eloquent/Pagination/EloquentQueryAdapterTest.php`
- [X] T009 [P] [US1] Add boundary coverage for empty, exact-page-size, full-page-with-next-row and out-of-range reads in `tests/Domain/Shared/Pagination/SliceTest.php`

### Implementation for User Story 1

- [X] T010 [US1] Implement lazy bounded page loading, probe-row trimming, `hasNext`, item iteration, mapping and count-free headers in `app/Domain/Shared/Pagination/Slice.php`
- [X] T011 [US1] Implement one Eloquent `limit(perPage + 1)` page read and preserve query criteria, ordering and offset in `app/Infrastructure/Laravel/Eloquent/Pagination/EloquentQueryAdapter.php`
- [X] T012 [US1] Update all paginated repository constructions to use the count-free Slice adapter while preserving root selection, criteria, relations and deterministic ordering in `app/Infrastructure/Laravel/Eloquent/{Person,Event,Group,Competition,Club,PersonPayment,PersonPrompt,ProtocolLine,RankCheck}/`
- [X] T013 [US1] Verify Application services continue to map the count-free Slice without transport DTO leakage in `app/Application/Service/{Person,Event,Group,Competition,Club,PersonPayment,PersonPrompt,ProtocolLine,RankCheck}/`

**Checkpoint**: The shared Slice algorithm is independently green and no paginated response path can obtain total/last-page metadata.

---

## Phase 4: User Story 2 - Единый контракт для API и SPA (Priority: P1)

**Goal**: API and SPA listings use `X-Pagination-Has-Next`, retain page/perPage navigation, and no longer depend on total or last page.

**Independent Test**: Request representative first, next and final pages through the API and navigate the corresponding SPA listings; verify headers, body shape, next/previous controls, filter reset and absence of old headers.

### Tests for User Story 2

- [X] T014 [P] [US2] Update representative API contract tests to assert Current-Page, Per-Page and Has-Next and assert missing Total/Last-Page in `tests/Feature/Api/V1/{Person,Event,Competition,Club,ProtocolLine,RankCheck}/`
- [X] T015 [P] [US2] Update shared pagination model tests for boolean Has-Next parsing and removal of total/lastPage state in `resources/spa/pages/listingModels.test.ts`
- [X] T016 [P] [US2] Add SPA component tests for next/previous availability, empty state and filter reset without total/lastPage in `resources/spa/components/ListingTable.test.ts`, `resources/spa/components/SlicePaginator.test.ts` and affected page test files

### Implementation for User Story 2

- [X] T017 [US2] Remove Total/Last-Page header assertions and emit Has-Next from the API response serialization path in `app/Bridge/Laravel/Http/Controllers/ApiAction.php`
- [X] T018 [US2] Migrate shared SPA pagination state and parsing from `total`/`lastPage` to `hasNext` in `resources/spa/api/types.ts`, `resources/spa/pages/listingModels.ts` and `resources/spa/components/ListingTable.vue`
- [X] T019 [US2] Migrate all paginated SPA pages and nested listing tables to disable next navigation from `hasNext` and enable previous navigation from `currentPage > 1` in `resources/spa/pages/**/` and `resources/spa/components/`
- [X] T020 [US2] Preserve page reset, loading, error, auth and filter behavior while updating list API wrappers to consume the new headers in `resources/spa/api/` and `resources/spa/pages/`

**Checkpoint**: API and SPA independently support the new header-only Slice contract with no fake pagination totals.

---

## Phase 5: User Story 3 - Быстрые списки с фильтрами и связями (Priority: P1)

**Goal**: Every paginated listing retains filter semantics and stable unique ordering while avoiding duplicate roots, unnecessary relations and N+1 queries.

**Independent Test**: Execute the listing matrix for every supported filter and relevant combination, compare unique result IDs and order across adjacent pages, and assert expected relation/query counts.

### Tests for User Story 3

- [ ] T021 [P] [US3] Add or extend repository integration tests for person filters by club, rank, IDs and name with stable ordering in `tests/Infrastructure/Laravel/Eloquent/Person/EloquentPersonRepositoryTest.php`
- [ ] T022 [P] [US3] Add or extend repository/API tests for event filters by competition, group, date, year and cup relation, including duplicate prevention in `tests/Infrastructure/Laravel/Eloquent/Event/EloquentEventRepositoryTest.php` and `tests/Feature/Api/V1/Event/`
- [ ] T023 [P] [US3] Add or extend repository integration tests for group, competition and club filters, aggregates and deterministic tie-breakers in `tests/Infrastructure/Laravel/Eloquent/{Group,Competition,Club}/`
- [ ] T024 [P] [US3] Add or extend repository integration tests for payment, prompt, protocol-line, rank-check and rank-check-row filters and parent joins in `tests/Infrastructure/Laravel/Eloquent/{PersonPayment,PersonPrompt,ProtocolLine,RankCheck}/`
- [ ] T025 [US3] Add listing query-count/N+1 regression coverage for representative relation-bearing endpoints in `tests/Feature/Api/V1/ListingQueryPerformanceTest.php`

### Implementation for User Story 3

- [X] T026 [US3] Apply the listing matrix to every paginated repository and document criteria, joins, distinct behavior, resources and stable ordering in `specs/027-count-free-slice-pagination/listing-matrix.md`
- [ ] T027 [US3] Refine relationship joins, `distinct`/root selection and typed resource loading only where the matrix identifies duplicate or over-fetching behavior in `app/Infrastructure/Laravel/Eloquent/{Person,Event,Group,Competition,Club,PersonPayment,PersonPrompt,ProtocolLine,RankCheck}/`
- [ ] T028 [US3] Add or correct deterministic unique tie-breakers after business ordering for every paginated query in `app/Infrastructure/Laravel/Eloquent/`
- [ ] T029 [US3] Add query-specific composite indexes only when baseline/explain evidence supports them, recording each decision in `specs/027-count-free-slice-pagination/query-profiles.md` and implementing migrations in `database/migrations/`
- [ ] T030 [US3] Remove unnecessary eager loads, `withCount` work or joins from unfiltered listing paths while preserving required DTO fields in `app/Infrastructure/Laravel/Eloquent/`

**Checkpoint**: All listing filters and relation paths retain their behavior and meet the no-duplicate/no-new-N+1 contract.

---

## Phase 6: User Story 4 - Безопасное улучшение схемы данных (Priority: P2)

**Goal**: Query and index improvements can be deployed without data loss, schema breakage or listing regressions.

**Independent Test**: Apply migrations to a production-like schema copy, compare data and API results before/after, and review query profiles for measurable improvement and rollback safety.

### Tests for User Story 4

- [ ] T031 [P] [US4] Add migration/schema integration coverage for index creation, existing data preservation and rollback where supported in `tests/Infrastructure/Laravel/Database/ListingIndexesTest.php`
- [ ] T032 [P] [US4] Add before/after query-profile validation for latency, SQL count and explain-plan changes in `tests/Feature/Api/V1/ListingQueryPerformanceTest.php`
- [ ] T033 [US4] Add final API regression coverage proving all paginated listing families return the same DTO semantics and new headers after index/query changes in `tests/Feature/Api/V1/ListingPaginationContractTest.php`

### Implementation for User Story 4

- [ ] T034 [US4] Finalize additive, reversible listing index migrations from the approved query profiles in `database/migrations/`
- [ ] T035 [US4] Record final baseline/after measurements and accepted trade-offs for each heavy listing in `specs/027-count-free-slice-pagination/query-profiles.md`
- [ ] T036 [US4] Verify migration execution on an existing-data schema copy and document deployment/rollback notes in `specs/027-count-free-slice-pagination/quickstart.md`

**Checkpoint**: Schema and query improvements are measured, data-safe and regression-tested.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Complete project-wide validation and documentation.

- [X] T037 [P] Update feature contracts and quickstart references after implementation in `specs/027-count-free-slice-pagination/contracts/` and `specs/027-count-free-slice-pagination/quickstart.md`
- [X] T038 [P] Remove obsolete total/lastPage pagination fixtures and update affected tests without weakening coverage in `tests/` and `resources/spa/`
- [X] T039 Run frontend typecheck and focused SPA tests for all migrated listing pages in `package.json` scripts and `resources/spa/`
- [ ] T040 Run `composer cs`, `composer stan`, `composer rector`/dry-run review, focused tests, full `composer test`, `git diff --check`, and the quickstart validation from `specs/027-count-free-slice-pagination/quickstart.md`
- [ ] T041 Verify the application starts and manually inspect representative API responses and SQL/query logs for no count query and no new N+1 in `specs/027-count-free-slice-pagination/query-profiles.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No code dependency; T002 depends on the inventory from T001.
- **Foundational (Phase 2)**: Depends on Setup; blocks all user stories.
- **User Story 1 (Phase 3)**: Depends on Phase 2; MVP and shared algorithm.
- **User Story 2 (Phase 4)**: Depends on US1 for the final Slice headers, though API test preparation can run after Phase 2.
- **User Story 3 (Phase 5)**: Depends on US1; can run in parallel with US2 after the shared seam is stable.
- **User Story 4 (Phase 6)**: Depends on the US3 query matrix and measured index candidates.
- **Polish (Phase 7)**: Depends on all desired stories.

### User Story Dependencies

- **US1**: No dependency on another story after Foundational; delivers the MVP.
- **US2**: Depends on the US1 Slice behavior; no dependency on query optimization details.
- **US3**: Depends on the US1 adapter; independent of SPA migration after API contract tests are available.
- **US4**: Depends on US3 evidence; validates and hardens the resulting schema/query changes.

### Parallel Opportunities

- T007–T009 can be written in parallel because they target separate test concerns/files.
- T014–T016 can be written in parallel across API, shared model and SPA tests.
- T021–T024 can be implemented in parallel by listing family.
- T029 index migrations can be parallelized by independent table/query profile after approval of the matrix.
- T031–T033 can be written in parallel before the final migration verification.

## Parallel Example: User Story 3

```text
Task T021: Person filter/order integration coverage
Task T022: Event relationship-filter and duplicate coverage
Task T023: Group/competition/club listing coverage
Task T024: Payment/prompt/protocol/rank listing coverage
```

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Setup and Foundational phases.
2. Write and pass the single shared no-COUNT `perPage + 1` regression test.
3. Implement the Slice and Eloquent bounded adapter.
4. Validate a representative API page and stop for MVP review.

### Incremental Delivery

1. Add US2 to migrate API and SPA navigation to Has-Next.
2. Add US3 to audit every filter, join, relation and ordering path.
3. Add US4 to apply measured indexes and verify migration safety.
4. Run the final project gates and quickstart.

### Final Definition of Done

- All tasks above are checked off.
- The shared regression test proves no `COUNT`, exact `perPage + 1`, trimming and `hasNext`.
- No API response exposes Total/Last-Page headers.
- Listing matrix, query profiles, API/UI contracts and quickstart are current.
- CS, STAN, Rector review, tests, frontend checks, application startup and N+1 review pass.
