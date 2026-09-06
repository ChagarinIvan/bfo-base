# Tasks: SPA-оплаты персоны

**Input**: Design documents from specs/016-persons-spa-payments/

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api.md

**Tests**: Included because the feature specification and user request require API/request,
Application and SPA coverage.

## Phase 1: Setup

- [X] T001 Review current payment routes, shared consumers and 015 SPA patterns in app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php, app/Repositories/ProtocolLinesRepository.php, and resources/spa/pages/persons/
- [X] T002 [P] Verify the payment feature artifacts and API contract in specs/016-persons-spa-payments/

## Phase 2: Foundational

- [X] T003 Update PersonPaymentDto validation to take personId from route parameters and date from request in app/Application/Dto/PersonPayment/PersonPaymentDto.php
- [X] T004 [P] Add payment Application unit coverage with repository/factory mocks and no Eloquent entities in tests/Application/Service/PersonPayment/
- [X] T005 [P] Add payment API route imports and middleware split in app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php

## Phase 3: User Story 1 — Просмотр оплат (P1) 🎯 MVP

**Goal**: Authenticated users can view a person's payments in SPA, with explicit loading, empty and not-found states.

**Independent Test**: Authenticated API list requests return payment DTOs for an existing person, empty array for
no payments, 404 for an unknown person and 401 without auth; Vitest verifies the SPA list route and states.

- [X] T006 [P] [US1] Add API list action and person existence handling in app/Bridge/Laravel/Http/Controllers/Api/V1/PersonPayment/ListPersonPaymentsAction.php and app/Application/Service/PersonPayment/ListPersonsPaymentsService.php
- [X] T007 [P] [US1] Add API request coverage for authenticated list, empty list, unknown person and stable ordering in tests/Feature/Api/V1/PersonPayment/ListPersonPaymentsActionTest.php
- [X] T008 [P] [US1] Add payment API types and list helper in resources/spa/api/types.ts and resources/spa/api/personPayments.ts
- [X] T009 [US1] Add SPA payment list page with person context, loading/error/empty/not-found states and stale-response guard in resources/spa/pages/persons/PersonPaymentsPage.vue
- [X] T010 [US1] Register /app/persons/:personId/payments and add list-page Vitest coverage in resources/spa/router/index.ts, resources/spa/pages/persons/PersonPaymentsPage.test.ts, and resources/spa/api/personPayments.test.ts

## Phase 4: User Story 2 — Добавление и обновление оплаты (P1)

**Goal**: Authenticated users can submit a date; the existing person/year record is created or updated without duplicates.

**Independent Test**: API request tests cover 401, 404, 422, create and same-year update; Vitest covers
form validation retention, pending state and successful navigation.

- [X] T011 [P] [US2] Add authenticated create/update action and response status in app/Bridge/Laravel/Http/Controllers/Api/V1/PersonPayment/CreateOrUpdatePersonPaymentAction.php
- [X] T012 [P] [US2] Add API request coverage for auth, validation, create, idempotent save and same-year update in tests/Feature/Api/V1/PersonPayment/CreateOrUpdatePersonPaymentActionTest.php
- [X] T013 [US2] Register protected POST /persons/{personId}/payments route in app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php
- [X] T014 [P] [US2] Add shared date form and create page with field errors, pending protection and return-to-list behavior in resources/spa/pages/persons/PersonPaymentForm.vue and resources/spa/pages/persons/CreatePersonPaymentPage.vue
- [X] T015 [US2] Add SPA create route and frontend API/form tests in resources/spa/router/index.ts, resources/spa/api/personPayments.ts, resources/spa/pages/persons/PersonPaymentForm.test.ts, and resources/spa/api/personPayments.test.ts

## Phase 5: User Story 3 — Завершение миграции (P2)

**Goal**: The person details link opens SPA, payment-only Blade entry points are removed, and shared consumers remain intact.

**Independent Test**: route regression and usage audit verify old payment routes are absent while
protocol/rank payment queries and person details still work.

- [X] T016 [P] [US3] Change the payments link to /app/persons/{personId}/payments in resources/views/persons/show.blade.php and add route regression assertions in tests/Feature/PersonsRoutesTest.php
- [X] T017 [US3] Remove audited payment-only web routes/actions/views from app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php, app/Bridge/Laravel/Http/Controllers/PersonPayment/, and resources/views/person-payment/
- [X] T018 [US3] Run shared payment consumer regression coverage and verify app/Repositories/ProtocolLinesRepository.php, app/Infrastructure/Laravel/Eloquent/PersonPayment/, and app/Domain/PersonPayment/ remain available

## Phase 6: Polish & Cross-Cutting Concerns

- [X] T019 [P] Update SPA translations for payment title, date, loading, empty, not-found, validation and save errors in resources/lang/by.json
- [X] T020 [P] Add query-count/N+1 regression assertion for the authenticated payment list in tests/Feature/Api/V1/PersonPayment/ListPersonPaymentsActionTest.php
- [X] T021 Run targeted backend/frontend tests, git diff --check, composer cs, composer stan, composer rector and inspect final route/usages audit.

## Phase 7: Follow-up — общий paginated payment query и domain events

- [X] T022 Move payment API list/mutation to `/persons/payments`; use query `personId`, `year`, `page`, `perPage` for list and required `personId` in POST body.
- [X] T023 Add `Slice` pagination and year criteria to payment repository/application service; cover year filtering, pagination and required person id in API tests.
- [X] T024 Extract reusable SPA `YearFilter` component and use it for competitions, group events and person payments; update API/page Vitest coverage.
- [X] T025 Add `PersonPaymentCreated` and `PersonPaymentUpdated` domain events, aggregate lifecycle methods and integration assertions.

## Dependencies & Execution Order

- T001–T005 establish the contract and shared validation; T003 and T005 block API implementation.
- US1 (T006–T010) is the MVP and depends on Phase 2.
- US2 (T011–T015) depends on the API route foundation and can reuse US1's person context.
- US3 (T016–T018) runs after the new SPA/API paths are verified, before deleting legacy entry points.
- Polish T019–T021 follows all stories.

## Parallel Opportunities

- T002, T004 and T005 can proceed in parallel after T001.
- T007 and T008 can proceed in parallel with the list action; T011 and T012 are independent after
  the command contract is fixed; T014 can proceed in parallel with backend tests.
- T019 and T020 are parallel after the stories.

## Implementation Strategy

1. Deliver authenticated payment list as MVP (US1).
2. Add authenticated create-or-update form (US2).
3. Update entry link and remove only audited legacy payment UI (US3).
4. Run all quality gates and acceptance scenarios once at the end.
