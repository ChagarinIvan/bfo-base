# Tasks: SPA Cup Forms and Legacy Route Removal

**Input**: Design documents from `/specs/029-cups-spa-forms/`

**Tests**: Required by FR-012 and the project TDD constitution. Tests are written before implementation.

## Phase 1: Setup

- [X] T001 Confirm current cup route/API baselines and preserve the dirty worktree in `specs/029-cups-spa-forms/research.md`
- [X] T002 [P] Add Belarusian cup-form translation keys in `resources/lang/by.json`

## Phase 2: Foundational

- [X] T003 Define shared cup form request/response types in `resources/spa/api/types.ts`
- [X] T004 [P] Add cup API client functions for list, view, create, and update in `resources/spa/api/cups.ts`
- [X] T005 [P] Add API create/view/update route registrations in `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [X] T006 Verify authenticated `UserId` context and shared DTO serialization for cup actions in `app/Bridge/Laravel/Http/Controllers/ApiAction.php`

## Phase 3: User Story 1 - Create a cup in SPA (Priority: P1) 🎯 MVP

**Independent test**: API request tests and SPA page tests prove authenticated creation, 422 field errors,
pending state, success navigation, and no mutation on invalid input.

- [X] T007 [P] [US1] Add API request tests for authenticated create, unauthenticated rejection, exact payload, validation, and audit fields in `tests/Feature/Api/V1/Cup/CupFormActionsTest.php`
- [X] T008 [P] [US1] Add shared cup form component tests for required fields, payload emission, pending state, and field errors in `resources/spa/pages/cups/CupForm.test.ts`
- [X] T009 [US1] Implement authenticated create action delegating to `AddCupService` in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/CreateCupAction.php`
- [X] T010 [US1] Add create form state and validation presentation in `resources/spa/pages/cups/CupForm.vue`
- [X] T011 [US1] Implement SPA create page with API submission, error mapping, toast, and listing navigation in `resources/spa/pages/cups/CreateCupPage.vue`
- [X] T012 [US1] Register guarded `/app/cups/create` route and update the cups listing create link in `resources/spa/router/index.ts` and `resources/spa/pages/cups/CupsPage.vue`

## Phase 4: User Story 2 - Edit a cup in SPA (Priority: P1)

**Independent test**: API request tests and SPA edit-page tests prove loading, prefilled values, update,
not-found, validation errors, pending state, and success navigation.

- [X] T013 [P] [US2] Extend API request tests for view/update payload, not-found, unauthenticated access, validation, and persistence in `tests/Feature/Api/V1/Cup/CupFormActionsTest.php`
- [X] T014 [P] [US2] Add edit page tests for loading, initial values, submit, API errors, and success navigation in `resources/spa/pages/cups/EditCupPage.test.ts`
- [X] T015 [US2] Implement authenticated view and update actions delegating to existing cup services in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ViewCupAction.php` and `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/UpdateCupAction.php`
- [X] T016 [US2] Implement API view/update client functions and edit page state in `resources/spa/api/cups.ts` and `resources/spa/pages/cups/EditCupPage.vue`
- [X] T017 [US2] Register guarded `/app/cups/:id/edit` route and make the cup edit action target the SPA route in `resources/spa/router/index.ts` and `resources/spa/components/actions/CupActionMenu.vue`

## Phase 5: User Story 3 - Retire legacy cup form routes (Priority: P2)

**Independent test**: legacy create/edit page and mutation URLs are unavailable while detail, table, event,
export, and delete routes remain available.

- [X] T018 [P] [US3] Add regression tests for removed legacy form URLs and preserved cup routes in `tests/Bridge/Laravel/Http/Controllers/Cup/CupFormRoutesTest.php`
- [X] T019 [US3] Remove legacy create/edit/store/update route registrations and unused imports in `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`
- [X] T020 [US3] Remove legacy cup form actions `app/Bridge/Laravel/Http/Controllers/Cup/ShowCreateCupFormAction.php`, `ShowEditCupFormAction.php`, `StoreCupAction.php`, and `UpdateCupAction.php`
- [X] T021 [US3] Remove legacy Blade form templates `resources/views/cup/create.blade.php` and `resources/views/cup/edit.blade.php` and update references/tests

## Phase 6: Polish and verification

- [X] T022 [P] Add router/API/component coverage for authenticated route guards and Belarusian labels in `resources/spa/router/index.test.ts` and cup SPA tests
- [X] T023 Run `composer cs`, `composer stan`, focused PHP tests, and `git diff --check` per `specs/029-cups-spa-forms/quickstart.md`
- [X] T024 Run `npm run ci` and verify the completed acceptance scenarios in `specs/029-cups-spa-forms/quickstart.md`
- [X] T025 Mark all completed tasks in this file and verify no legacy create/edit route or template reference remains

## Dependencies & Execution Order

- T001–T006 are foundational; T007–T012 follow T003–T006.
- US2 depends on the shared form/API conventions from US1 but has independent acceptance tests.
- US3 can be prepared in parallel with US2, but route/template deletion occurs after SPA links and tests are in place.
- T023–T025 run only after all stories are implemented.

## Parallel Opportunities

- T002, T004, and T005 can proceed in parallel after the baseline is confirmed.
- T007/T008 and T013/T014 are parallel test-writing tasks, but implementation follows each story's tests.
- T018 can be written while US2 implementation is in progress because it targets separate legacy route files.

## Implementation Strategy

1. Build and validate create as the MVP.
2. Add edit using the same shared form and API contract.
3. Remove legacy form paths only after SPA routes and regression tests exist.
4. Run all quality gates once at the end.
