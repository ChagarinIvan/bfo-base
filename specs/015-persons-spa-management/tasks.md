# Tasks: SPA-управление персональными промптами

**Input**: Design documents from `specs/015-persons-spa-management/`

**Organization**: Tasks grouped by user story; tests are required by FR-013.

## Phase 1: Setup

- [X] T001 [P] Inventory existing PersonPrompt API, web routes, Blade views and shared usages in `app/Bridge/Laravel/Provider`, `app/Bridge/Laravel/Http/Controllers/PersonPrompt`, `resources/views/person-prompt`, `resources/views/persons/show.blade.php`, and `app/Services`
- [X] T002 [P] Confirm existing API serializer, pagination headers and auth middleware patterns in `app/Bridge/Laravel/Http/Controllers/Api/V1` and `resources/spa/api`

## Phase 2: Foundational

- [X] T003 Add PersonPrompt paginated criteria/query support and preserve stable `id DESC` ordering in `app/Application/Dto/PersonPrompt/SearchPersonPromptDto.php` and `app/Infrastructure/Laravel/Eloquent/PersonPrompt/EloquentPromptPaymentRepository.php`
- [X] T004 Add API route binding/serializer registration for PersonPrompt in `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php` and the existing API serialization configuration
- [X] T005 [P] Add shared frontend PersonPrompt types and API client helpers in `resources/spa/api/types.ts` and `resources/spa/api/personPrompts.ts`

## Phase 3: User Story 1 — Просмотреть промпты персоны (P1) 🎯 MVP

**Goal**: Public users can open a person’s paginated prompt list with correct loading, empty, not-found and error states.

**Independent test**: API request tests and Vitest verify a populated list, empty list, unknown person, pagination and anonymous action visibility at `/app/persons/{personId}/prompts`.

- [X] T006 [US1] Add paginated authenticated list API action and request tests for 401/404/422 cases and pagination headers in `app/Bridge/Laravel/Http/Controllers/Api/V1/PersonPrompt/ListPersonPromptsAction.php` and `tests/Feature/Api/V1/PersonPrompt/PersonPromptApiTest.php`
- [X] T007 [US1] Extend `app/Application/Service/PersonPrompt/ListPersonsPrompts.php`, `app/Application/Service/PersonPrompt/ListPersonsPromptsService.php`, and repository query flow to accept pagination without loading unrelated rows
- [X] T008 [P] [US1] Add SPA route, list page, pagination model and stale-response guard in `resources/spa/router/index.ts`, `resources/spa/pages/personPrompts/PersonPromptsPage.vue`, and `resources/spa/pages/personPrompts/personPromptsModels.ts`
- [X] T009 [P] [US1] Add list-page Vitest coverage for populated/empty/not-found/error/loading states, pagination and anonymous actions in `resources/spa/pages/personPrompts/PersonPromptsPage.test.ts` and `resources/spa/api/personPrompts.test.ts`
- [X] T010 [US1] Replace the person detail prompt link with `/app/persons/{personId}/prompts` in `resources/views/persons/show.blade.php` and add route regression coverage in `tests/Feature/PersonsRoutesTest.php`

## Phase 4: User Story 2 — Создать и отредактировать промпт (P1)

**Goal**: Authenticated users can create/edit a prompt in SPA with existing validation and without losing input.

**Independent test**: API/request tests cover auth, 404 and 422; Vitest covers create/edit form, field errors, loading and duplicate submit protection.

- [X] T011 [US2] Add authenticated create/update API actions and routes using commands and existing Application services in `app/Bridge/Laravel/Http/Controllers/Api/V1/PersonPrompt/` and `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [X] T012 [P] [US2] Add create/update API request tests for 401, 404, 422 field errors, metaphone recalculation and successful persistence in `tests/Feature/Api/V1/PersonPrompt/CreatePersonPromptActionTest.php` and `tests/Feature/Api/V1/PersonPrompt/UpdatePersonPromptActionTest.php`
- [X] T013 [US2] Add SPA create/edit routes and shared form with auth guard, prefilled value, field errors and disabled repeat submit in `resources/spa/pages/personPrompts/CreatePersonPromptPage.vue`, `resources/spa/pages/personPrompts/EditPersonPromptPage.vue`, and `resources/spa/pages/personPrompts/PersonPromptForm.vue`
- [X] T014 [P] [US2] Add frontend API helpers and Vitest coverage for form transitions, validation retention, auth behavior and saving state in `resources/spa/api/personPrompts.ts` and `resources/spa/pages/personPrompts/PersonPromptForm.test.ts`

## Phase 5: User Story 3 — Удалить промпт (P2)

**Goal**: Authenticated users can confirm and delete a prompt, with correct refresh and error behavior.

**Independent test**: API tests and Vitest verify cancel, success, already-deleted/error, last-page and no duplicate delete requests.

- [X] T015 [US3] Add authenticated delete API action/route and request tests for 401, 404, success and no false-success response in `app/Bridge/Laravel/Http/Controllers/Api/V1/PersonPrompt/DeletePersonPromptAction.php`, `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`, and `tests/Feature/Api/V1/PersonPrompt/DeletePersonPromptActionTest.php`
- [X] T016 [US3] Add delete confirmation, pending state, error handling and page refresh behavior to `resources/spa/pages/personPrompts/PersonPromptsPage.vue`
- [X] T017 [US3] Add delete interaction tests for cancel, success, error, last-page fallback and duplicate-submit protection in `resources/spa/pages/personPrompts/PersonPromptsPage.test.ts`

## Phase 6: User Story 4 — Клубный контекст и legacy cleanup (P2)

**Goal**: Club participant tables omit the redundant club column and prompt-only legacy entry points are removed without breaking shared consumers.

**Independent test**: SPA club tests, route list/usages audit, and regression tests confirm the column is absent and shared prompt consumers still work.

- [X] T018 [P] [US4] Add explicit club-context visibility prop to `resources/spa/components/PersonTable.vue` and pass it from `resources/spa/pages/clubs/ClubDetailsPage.vue`; preserve global persons table behavior
- [X] T019 [P] [US4] Add/adjust club detail Vitest coverage for hidden club header/cells and preserved fields/actions in `resources/spa/pages/clubs/ClubDetailsPage.test.ts` and `resources/spa/components/PersonTable.test.ts`
- [X] T020 [US4] Remove prompt-only web route registrations and old Blade actions/views from `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, `app/Bridge/Laravel/Http/Controllers/PersonPrompt/`, `resources/views/person-prompt/`, and `resources/views/persons/prompts.blade.php` only after T001 usage audit
- [X] T021 [US4] Add route regression assertions that removed prompt web routes are absent and standard Laravel unknown-route handling remains in `tests/Feature/PersonsRoutesTest.php`
- [X] T022 [US4] Run shared consumer regression tests for parser/import/rank/person disable flows, remove legacy `PersonPromptService`, add `active` migration, and preserve required Application/repository consumers

## Phase 7: Polish & Cross-Cutting Concerns

- [X] T023 [P] Audit API and SPA strings/translations and update `resources/spa/i18n.ts` and `resources/lang/{ru,by,en}.json` for loading, empty, validation, not-found, confirmation and error states
- [X] T024 [P] Add query-count/N+1 regression assertions for paginated prompt list in `tests/Feature/Api/V1/PersonPrompt/` and inspect generated query scope
- [X] T025 Run `quickstart.md`, targeted tests, `git diff --check`, `composer cs`, `composer stan`, `composer rector`, `composer test`, and `npm run ci`; inspect final route/usages audit

## Dependencies & Execution Order

- Setup T001–T002 precedes foundational work; T003–T004 block API stories, T005 blocks SPA work.
- US1 (T006–T010) is the MVP and depends on Phase 2.
- US2 (T011–T014) depends on shared API/SPA foundations and may reuse US1 list route/form components.
- US3 (T015–T017) depends on the US1 list refresh path and can follow US2 for complete CRUD.
- US4 (T018–T022) depends on T001 audit and can run in parallel with US2/US3 where files do not overlap.
- Polish T023–T025 follows all desired stories.

## Parallel Opportunities

- T001–T002; T005; T006 and T008–T009; T011–T012; T018–T019; T023–T024 can be parallelized where dependencies permit.
- Backend API tests and independent SPA model/page tests can proceed in parallel after their shared contracts exist.

## Implementation Strategy

1. Complete setup/foundation and deliver US1 as MVP.
2. Add authenticated create/edit, then delete for complete prompt CRUD.
3. Apply club-context correction and audited legacy cleanup.
4. Run the complete quality gate and acceptance scenarios once at the end.
