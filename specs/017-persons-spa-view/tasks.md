# Tasks: SPA View Person

**Input**: Design documents from specs/017-persons-spa-view/

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api.md, quickstart.md

## Phase 1: Setup

- [x] T001 Verify the existing SPA shell, PersonPromptPersonInfo, FilterPanel, YearFilter, DateFilter, paginator and optional-auth API route conventions in the files referenced by plan.md

## Phase 2: Foundational

- [x] T002 [P] Define ProtocolLineResources and extend ProtocolLineRepository with a paginated read contract in app/Domain/ProtocolLine/ProtocolLineResources.php and app/Domain/ProtocolLine/ProtocolLineRepository.php
- [x] T003 [P] Confirm the existing API client/type extension points for protocol lines in resources/spa/api/types.ts and resources/spa/api/protocolLines.ts
- [x] T004 [P] Confirm the optional-auth route insertion point for ListProtocolLinesAction in app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php

## Phase 3: User Story 2 - Protocol-line API (Priority: P1)

**Goal**: Provide a paginated, filtered list of one person’s protocol lines with explicit event and competition resources.

**Independent Test**: PHPUnit application and feature tests cover criteria/resources, filters, active-person filtering, 422/auth behavior, pagination and query count.

### Tests first

- [x] T005 [P] [US2] Add ListProtocolLinesService unit tests for person validation, criteria, resources and DTO mapping in tests/Application/Service/ProtocolLine/ListProtocolLinesServiceTest.php
- [x] T006 [P] [US2] Add API request tests for valid pagination, relation flags, year/name/date filters, empty list, unknown/inactive person, malformed input and auth policy in tests/Feature/Api/V1/ProtocolLine/ListProtocolLinesActionTest.php
- [x] T007 [US2] Add a query-count regression assertion proving protocol-line relation loading does not issue one query per returned row in tests/Feature/Api/V1/ProtocolLine/ListProtocolLinesActionTest.php

### Implementation

- [x] T008 [US2] Implement SearchProtocolLineDto with validation and normalisation for personId, year, competitionName, date, withEvent and withCompetition in app/Application/Dto/ProtocolLine/SearchProtocolLineDto.php
- [x] T009 [US2] Implement ViewProtocolLineDto and ProtocolLineAssembler for all table fields and optional relation projections in app/Application/Dto/ProtocolLine/ViewProtocolLineDto.php and app/Application/Dto/ProtocolLine/ProtocolLineAssembler.php
- [x] T010 [US2] Implement ListProtocolLines command exposing Criteria and ProtocolLineResources in app/Application/Service/ProtocolLine/ListProtocolLines.php
- [x] T011 [US2] Implement ListProtocolLinesService with repository pagination and assembler mapping; leave active-person filtering to the repository in app/Application/Service/ProtocolLine/ListProtocolLinesService.php
- [x] T012 [US2] Implement paginated protocol-line filtering, active-person filtering, stable event-date/id ordering and typed eager loading in app/Infrastructure/Laravel/Eloquent/ProtocolLine/EloquentProtocolLinesRepository.php; keep non-standard cup/identification operations in app/Repositories/ProtocolLinesRepository.php
- [x] T013 [US2] Implement ListProtocolLinesAction pagination wiring and register GET /api/v1/protocol-lines under optional authentication in app/Bridge/Laravel/Http/Controllers/Api/V1/ProtocolLine/ListProtocolLinesAction.php and app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php

## Phase 4: User Story 1 - Person page and actions (Priority: P1)

**Goal**: Render the public SPA View Person page with the shared Person Info module and five preserved destinations.

**Independent Test**: Vitest mounts an existing and unknown-person page, verifies Person Info placement, five destinations, auth visibility and loading/error states.

### Tests first

- [x] T014 [P] [US1] Add PersonViewPage component tests for person context, five action destinations, anonymous visibility, loading/error/empty states and stale person requests in resources/spa/pages/persons/PersonViewPage.test.ts
- [x] T015 [P] [US1] Add router coverage for /app/persons/:personId and its public access policy in resources/spa/router/index.test.ts

### Implementation

- [x] T016 [US1] Add persistent PersonLayoutPage with PersonPromptPersonInfo, shared edit/payments/prompts/ranks navigation and nested RouterView; keep participation content in PersonViewPage in resources/spa/pages/persons/PersonLayoutPage.vue, resources/spa/components/PersonInfoNavigation.vue and resources/spa/pages/persons/PersonViewPage.vue
- [x] T017 [US1] Register /app/persons/:personId as the parent route and nest view, payments and prompts sections under it in resources/spa/router/index.ts
- [x] T018 [US1] Update the existing person list/detail navigation to point to /app/persons/:personId where the View Person destination is exposed in resources/spa/components/PersonTable.vue and related components
- [x] T019 [US1] Add page/action and not-found/loading translations in resources/lang/by.json

## Phase 5: User Story 3 - Participation table (Priority: P1)

**Goal**: Show the person’s protocol lines in the SPA with links, columns and pagination.

**Independent Test**: Vitest verifies API query construction and table rendering for competition/event/result fields and pagination.

### Tests first

- [x] T020 [P] [US3] Add protocolLines API helper tests for required personId, withEvent/withCompetition flags, filters and pagination in resources/spa/api/protocolLines.test.ts
- [x] T021 [US3] Extend PersonViewPage tests for table columns, competition/event links, empty state and paginator callbacks in resources/spa/pages/persons/PersonViewPage.test.ts

### Implementation

- [x] T022 [US3] Implement getProtocolLines and ProtocolLine TypeScript types for the paginated API in resources/spa/api/protocolLines.ts and resources/spa/api/types.ts
- [x] T023 [US3] Add participation table with nine Blade-equivalent columns, competition/event links and shared paginator to resources/spa/pages/persons/PersonViewPage.vue

## Phase 6: User Story 4 - Participation filters (Priority: P1)

**Goal**: Filter the participation table by year, competition name and event date with shared controls and stale-response protection.

**Independent Test**: Vitest and API tests verify each query parameter, debounce, first-page reset, field validation and response ordering.

### Tests first

- [x] T024 [P] [US4] Add page model/query tests for year, competitionName, date, short-name debounce and page reset in resources/spa/pages/persons/personViewModels.test.ts
- [x] T025 [US4] Extend API request tests for each filter and invalid filter formats in tests/Feature/Api/V1/ProtocolLine/ListProtocolLinesActionTest.php

### Implementation

- [x] T026 [US4] Add reusable person-view query/debounce/filter helpers in resources/spa/pages/persons/personViewModels.ts
- [x] T027 [US4] Add one FilterPanel containing YearFilter, competition-name InputText and DateFilter, including validation and first-page reset, in resources/spa/pages/persons/PersonViewPage.vue
- [x] T028 [US4] Add stale-request protection and loading/error/empty/not-found transitions for filtered participation loads in resources/spa/pages/persons/PersonViewPage.vue

## Phase 7: Polish and validation

- [x] T029 [P] Update API and SPA tests/fixtures/translations without introducing PHPUnit notices or deprecations
- [x] T030 [P] Update specs/017-persons-spa-view/quickstart.md if implementation paths or commands changed
- [x] T031 Run focused PHPUnit/Vitest tests, then composer cs, composer stan, composer rector -- --dry-run, npm run ci and git diff --check
- [x] T032 Verify route order, API query count and acceptance scenarios against specs/017-persons-spa-view/spec.md, plan.md and contracts/api.md

## Phase 8: Review fixes

- [x] T033 [US1] Keep the shared Person Info card mounted while switching between person SPA tabs and cover the parent/child route structure in resources/spa/router/index.test.ts and resources/spa/components/PersonInfoNavigation.test.ts
- [x] T034 [US4] Prevent short competition-name input from sending validation requests, disabling the field or losing focus; cover the behavior in resources/spa/pages/persons/PersonViewPage.test.ts
- [x] T035 [US1] Hide created/updated impression rows for anonymous visitors and guard stale Person Info responses in resources/spa/components/PersonPromptPersonInfo.vue and resources/spa/components/PersonPromptPersonInfo.test.ts
- [x] T036 [US1] Add the competitions tab to shared Person Info navigation, returning to the person participation page, with SPA coverage and updated acceptance documentation
- [x] T037 [US3] Add mismatch detection for populated protocol-line name and birth-year fields, row highlighting, and authenticated extraction-action coverage in resources/spa/pages/persons/personViewModels.test.ts and resources/spa/pages/persons/PersonViewPage.test.ts
- [x] T038 [US3] Restore the existing authenticated person-extraction action in the SPA participation table and pass the current person context from the persistent Person Info module in resources/spa/pages/persons/PersonViewPage.vue, resources/spa/pages/persons/PersonLayoutPage.vue and resources/spa/components/PersonPromptPersonInfo.vue

## Dependencies and execution order

- T001 precedes all implementation work.
- T002-T004 are foundational; T005-T007 must be written before T008-T013.
- T008-T013 complete the API needed by T022-T023 and T027-T028.
- T014-T019 can proceed independently from backend implementation except for final integration.
- T020-T023 depend on the API contract and T016.
- T024-T028 depend on the basic table and API query shape.
- T029-T032 are final validation tasks after all user stories.

## Parallel opportunities

- T002-T004 can run in parallel after T001.
- T005 and T006 can run in parallel; T007 follows the API fixture shape.
- T014-T015 can run in parallel with T005-T007.
- T020 and T024 can run in parallel once the API contract is fixed.
- T029-T030 can run in parallel before T031-T032.

## MVP

The MVP is T001-T023: public person page, shared Person Info, five preserved actions, protocol-line API, table, links and pagination. Filters and race-condition hardening complete the MVP acceptance quality in T024-T028.
