# Tasks: Удаление legacy competition-раздела

**Input**: Design documents from `/specs/013-remove-legacy-competition/`

**Prerequisites**: [plan.md](plan.md), [spec.md](spec.md), [research.md](research.md),
[data-model.md](data-model.md), [contracts/removal-contract.md](contracts/removal-contract.md),
[quickstart.md](quickstart.md)

**Tests**: Included because the feature changes route registration, navigation and legacy behavior.

## Phase 1: Setup

**Purpose**: Establish the repository inventory and feature baseline.

- [X] T001 Build a complete legacy competition inventory across `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, `app/Bridge/Laravel/Http/Controllers/Competition/`, `resources/views/competitions/`, `resources/views/`, `resources/spa/`, `tests/` and `specs/`, classifying each match as removable legacy, retained API/domain, canonical SPA or retained related-section behavior.
- [X] T002 [P] Verify the current target files `resources/spa/router/index.ts`, `resources/spa/pages/competitions/CompetitionsPage.vue`, `resources/spa/pages/competitions/CompetitionDetailsPage.vue`, `resources/spa/pages/competitions/CreateCompetitionPage.vue` and `resources/spa/pages/competitions/EditCompetitionPage.vue` against [contracts/removal-contract.md](contracts/removal-contract.md).
- [X] T003 [P] Record the final inventory and any intentional retained references in `specs/013-remove-legacy-competition/research.md`.

## Phase 2: Foundational

**Purpose**: Protect the target-layer contracts before deleting legacy presentation code.

- [X] T004 Confirm that `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/`, `app/Application/Service/Competition/` and `app/Domain/Competition/` provide all data and mutation behavior required by the existing SPA; do not delete or relocate these files.
- [X] T005 [P] Run focused baseline checks for `tests/Feature/Api/V1/Competition/`, `resources/spa/router/index.test.ts`, `resources/spa/pages/competitions/` and `resources/spa/api/competitions.test.ts` before legacy cleanup.

## Phase 3: User Story 1 — Открыть соревнование в едином интерфейсе (Priority: P1) 🎯 MVP

**Goal**: Ensure list, details and authenticated competition forms use the existing SPA, or add only
the missing screen discovered by the inventory.

**Independent Test**: SPA router/page tests resolve all four canonical paths and the SPA uses the
existing V1 competition API without a Blade competition page.

### Tests for User Story 1

- [X] T006 [P] [US1] Add or update canonical route assertions for `/app/competitions`, `/app/competitions/:id`, `/app/competitions/create` and `/app/competitions/:id/edit` in `resources/spa/router/index.test.ts`.
- [X] T007 [P] [US1] Add or update page-level regression assertions for list, details, create and edit behavior in `resources/spa/pages/competitions/CompetitionsPage.test.ts`, `resources/spa/pages/competitions/CompetitionDetailsPage.test.ts`, `resources/spa/pages/competitions/CreateCompetitionPage.test.ts` and `resources/spa/pages/competitions/EditCompetitionPage.test.ts`.

### Implementation for User Story 1

- [X] T008 [US1] If T002 finds a missing target screen, implement that screen under `resources/spa/pages/competitions/` using the existing SPA components, router conventions and `resources/spa/api/competitions.ts`; otherwise document that no new screen is required.
- [X] T009 [P] [US1] Verify the application shell, navbar and fallback destinations use `/app/competitions` in `resources/spa/components/AppLayout.vue`, `resources/views/layouts/navbar.blade.php`, `resources/views/errors/404error.blade.php`, `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, `app/Bridge/Laravel/Http/Controllers/Login/SignInAction.php` and `app/Bridge/Laravel/Http/Controllers/Registration/SendRegistrationDataAction.php`; update only stale legacy targets.
- [X] T010 [US1] Verify direct refresh/loading behavior for all `/app/competitions*` routes in `resources/spa/main.ts`, `resources/spa/App.vue` and the SPA shell configuration; preserve the existing Nginx/web fallback behavior.

**Checkpoint**: The competition list, details and authenticated forms work through one SPA entry
point and continue using the existing API contracts.

## Phase 4: User Story 2 — Удалить старые competition entry points (Priority: P1)

**Goal**: Remove every obsolete competition web route, presentation action, Blade view, stale import
and test while proving no old mutation route remains.

**Independent Test**: Requests and route inventory checks for all paths in
[contracts/removal-contract.md](contracts/removal-contract.md) show no registered legacy route,
rendered Blade page or data mutation.

### Tests for User Story 2

- [X] T011 [P] [US2] Extend route regression coverage in `tests/Feature/Competition/CompetitionNavigationTest.php` for every legacy list/show/create/edit/store/update/delete method identified by T001, asserting absence and non-mutation.
- [X] T012 [P] [US2] Add a focused source-inventory regression check in `tests/Feature/Competition/CompetitionNavigationTest.php` that protects the absence of removed production actions/views and legacy web URL references.

### Implementation for User Story 2

- [X] T013 [US2] Remove obsolete competition route registrations and related imports from `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, retaining only the root/fallback behavior and unrelated routes required by the migration.
- [X] T014 [P] [US2] Delete legacy competition presentation and mutation actions from `app/Bridge/Laravel/Http/Controllers/Competition/` after T001 confirms they have no retained consumers: list, show, create-form, edit-form, store, update and delete actions.
- [X] T015 [P] [US2] Delete legacy competition Blade views from `resources/views/competitions/` after T001 confirms they are no longer rendered.
- [X] T016 [P] [US2] Remove stale `use` statements and old competition URLs from retained Blade/controllers in `resources/views/`, `app/Bridge/Laravel/Http/Controllers/` and `app/Bridge/Laravel/Provider/` without removing event/protocol/cup behavior.
- [X] T017 [US2] Remove or rewrite tests in `tests/Bridge/Laravel/Http/Controllers/Competition/` that only test deleted legacy pages or mutations, preserving API tests in `tests/Feature/Api/V1/Competition/` and retained transition tests.
- [X] T018 [US2] Run the route inventory and focused PHPUnit suite, confirming all paths in `specs/013-remove-legacy-competition/contracts/removal-contract.md` are absent and no old handler can modify competition data.

**Checkpoint**: Legacy competition presentation is fully cleaned from routes, actions, views, stale
imports and obsolete tests; V1 API and domain/application code remain intact.

## Phase 5: User Story 3 — Сохранить переходы из неперенесённых разделов (Priority: P2)

**Goal**: Keep event/protocol/cup and other transitional flows working while their competition links
point to the canonical SPA.

**Independent Test**: Representative retained pages link to `/app/competitions/{id}` and their own
existing event/protocol routes still respond successfully.

### Tests for User Story 3

- [X] T019 [P] [US3] Update or add rendered-link assertions in `tests/Bridge/Laravel/Http/Controllers/Event/ShowEventActionTest.php`, `tests/Bridge/Laravel/Http/Controllers/CupEvents/ShowCupEventGroupActionTest.php`, `tests/Bridge/Laravel/Http/Controllers/Person/ShowPersonActionTest.php` and `tests/Feature/Competition/CompetitionNavigationTest.php` for canonical competition SPA destinations.
- [X] T020 [P] [US3] Verify retained event/protocol/cup route behavior in the relevant existing tests under `tests/Bridge/Laravel/Http/Controllers/Event/` and `tests/Bridge/Laravel/Http/Controllers/CupEvents/`.

### Implementation for User Story 3

- [X] T021 [US3] Update stale competition links in `resources/views/events/`, `resources/views/groups/`, `resources/views/persons/`, `resources/views/flags/` and `resources/views/cup/` to `/app/competitions...`, keeping event/protocol links unchanged.
- [X] T022 [US3] Verify redirects after retained event create/update/delete actions in `app/Bridge/Laravel/Http/Controllers/Event/ShowEventAction.php`, `app/Bridge/Laravel/Http/Controllers/Event/UpdateEventAction.php` and `app/Bridge/Laravel/Http/Controllers/Event/DeleteEventAction.php` still target the SPA details path.

**Checkpoint**: Transitional sections remain usable and every tested competition link opens the SPA.

## Phase 6: Polish & Cross-Cutting Concerns

- [X] T023 [P] Update `specs/013-remove-legacy-competition/research.md`, `specs/013-remove-legacy-competition/data-model.md` and `specs/013-remove-legacy-competition/contracts/removal-contract.md` with the final inventory and any explicitly retained references.
- [X] T024 [P] Run repository-wide searches for removed competition actions, Blade views and legacy web URLs, and check that no new N+1 or unbounded request pattern was introduced in `resources/spa/pages/competitions/`.
- [X] T025 Run the scenarios in `specs/013-remove-legacy-competition/quickstart.md`, including focused tests, `composer cs`, `composer stan`, `composer rector -- --dry-run`, `composer test` and `npm run ci`.
- [X] T026 Mark `specs/013-remove-legacy-competition/checklists/requirements.md` complete only after implementation evidence satisfies every requirement and acceptance scenario; do not use it as a substitute for the final quality gates.

## Dependencies & Execution Order

- Setup T001–T003 → Foundational T004–T005 → User Stories.
- US1 T006–T010 establishes and verifies the canonical SPA before legacy deletion.
- US2 T011–T018 depends on US1 and T001 inventory; it is the mandatory legacy-cleanup phase.
- US3 T019–T022 can be prepared after T001 but final verification follows US2 so no stale legacy
  target remains.
- Polish T023–T026 depends on all selected user-story work.

## Parallel opportunities

- T002, T003 and T005 can run in parallel after the repository baseline is identified.
- T006 and T007 can run in parallel because they cover different SPA test files.
- T014, T015 and T016 can run in parallel after T013 and T001, provided no file overlap is present.
- T019, T020 and T021 can be split across retained sections after the target path is agreed.
- T023 and T024 can run in parallel after implementation.

## Implementation Strategy

### MVP First (User Story 1 + required cleanup gate)

1. Complete T001–T005.
2. Verify or complete the existing SPA in T006–T010.
3. Execute T011–T018 to remove all confirmed legacy competition routes and pages.
4. Stop and validate canonical SPA navigation plus absence of legacy entry points.

### Incremental delivery

1. Deliver the existing SPA as the sole competition UI.
2. Deliver the complete legacy cleanup as one independently verifiable slice.
3. Verify retained event/protocol/cup links.
4. Run the final quality gates and quickstart.

## Notes

- Every task has an ID and an exact repository path.
- `[P]` is used only when the task can run independently without editing the same file as another
  parallel task.
- Do not delete files merely because they mention `Competition`; classify them against the inventory.
- Do not remove `app/Bridge/Laravel/Http/Controllers/Api/V1/Competition/`,
  `app/Application/Service/Competition/` or `app/Domain/Competition/` as part of legacy cleanup.
