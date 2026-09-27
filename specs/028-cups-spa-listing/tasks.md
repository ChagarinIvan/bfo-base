# Tasks: SPA-листинг кубков

## Dependencies

```text
T001 -> T002 -> T003 -> T004
T004 -> T005 -> T006 -> T007
T005 -> T008 -> T009
T006 -> T010 -> T011
T007, T009, T011 -> T012
```

User story order: US1 (public SPA listing) is the MVP; US2 (auth visibility) and US3
(cleanup/contract hardening) complete the feature. Tasks marked `[P]` touch independent
files and may run in parallel after their prerequisites.

## Phase 1: Setup

- [ ] T001 Confirm feature artifacts and existing dirty-worktree boundaries in `specs/028-cups-spa-listing/` and `git status`
- [ ] T002 [P] Add the cup listing API contract assertions outline in `specs/028-cups-spa-listing/contracts/cup-listing-api.md`
- [ ] T003 [P] Add localized SPA cup listing keys in `resources/lang/ru.json`, `resources/lang/by.json`, and `resources/spa/i18n.ts`

## Phase 2: Foundational backend

- [ ] T004 Add normalized `SearchCupDto` and command criteria with year/name/visibility validation and anonymous visibility policy in `app/Application/Dto/Cup/SearchCupDto.php` and `app/Application/Service/Cup/ListCup.php`
- [ ] T005 [P] Reuse `ViewCupDto` with authenticated impression groups and the existing `CupAssembler` without detail/event payloads in `app/Application/Dto/Cup/ViewCupDto.php` and `app/Application/Dto/Cup/CupAssembler.php`
- [ ] T006 [P] Extend the cup repository query port/adapter for name filtering and paginated listing criteria in `app/Domain/Cup/CupRepository.php` and `app/Infrastructure/Laravel/Eloquent/Cup/EloquentCupRepository.php`
- [ ] T007 Add focused Application tests for criteria normalization, anonymous visibility restriction, repository criteria, and compact mapping in `tests/Application/Service/Cup/ListCupServiceTest.php` and `tests/Application/Dto/Cup/SearchCupDtoTest.php`

## Phase 3: User Story 1 - Просмотреть кубки в SPA (Priority: P1)

**Independent test**: an anonymous API request and `CupsPage` component tests load a year-scoped
listing, search by name, paginate, and render empty/error/loading states plus existing links.

- [ ] T008 [US1] Add `ListCup` command and `ListCupService` returning `Slice<ViewCupDto>` in `app/Application/Service/Cup/ListCup.php` and `app/Application/Service/Cup/ListCupService.php`
- [ ] T009 [US1] Add thin `ListCupsAction`, optional-auth `/api/v1/cups` route, and API request coverage for year/name/pagination in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ListCupsAction.php`, `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`, and `tests/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ListCupsActionTest.php`
- [ ] T010 [P] [US1] Add typed cup API client/models and filter query helpers in `resources/spa/api/cups.ts`, `resources/spa/api/types.ts`, and `resources/spa/pages/cups/cupModels.ts`
- [ ] T011 [US1] Build `CupsPage.vue`, register `/app/cups`, update SPA navigation, and cover public list/filter/link states in `resources/spa/pages/cups/CupsPage.vue`, `resources/spa/router/index.ts`, `resources/spa/components/navigationModels.ts`, and `resources/spa/pages/cups/CupsPage.test.ts`

## Phase 4: User Story 2 - Управлять видимостью списка (Priority: P2)

**Independent test**: authenticated and anonymous API requests prove visibility isolation; SPA
tests prove the selector and authenticated columns are unavailable before auth and after logout.

- [ ] T012 [US2] Extend API request tests and `CupsPage` tests for visible/invisible/all modes,
  auth-only controls, logout reset, and denied anonymous hidden data in `tests/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ListCupsActionTest.php` and `resources/spa/pages/cups/CupsPage.test.ts`

## Phase 5: User Story 3 - Обслуживать listing через узкий API-контракт (Priority: P2)

**Independent test**: route inspection and contract tests prove `/cups` listing is retired while
detail/admin routes remain and API payload excludes detail-only fields.

- [ ] T013 [US3] Remove the Blade listing route/action and listing-only legacy `CupSearchDto`, command, service and obsolete tests while preserving detail/admin routes in `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`, `app/Bridge/Laravel/Http/Controllers/Cup/ShowCupsListAction.php`, `app/Application/Dto/Cup/CupSearchDto.php`, `app/Application/Service/Cup/ListLegacyCupService.php`, and `tests/Bridge/Laravel/Http/Controllers/Cup/ShowCupsListActionTest.php`
- [ ] T014 [US3] Update navbar/Blade references and add route regression assertions for `/app/cups` in `resources/views/layouts/navbar.blade.php`, `resources/spa/components/AppLayout.test.ts`, and `tests/Bridge/Laravel/Provider/WebRoutesServiceProviderTest.php`
- [ ] T015 [US3] Verify compact response contract and authenticated impression behavior in `tests/Bridge/Laravel/Http/Controllers/Api/V1/Cup/ListCupsActionTest.php` and `specs/028-cups-spa-listing/contracts/cup-listing-api.md`

## Phase 6: Polish and validation

- [ ] T016 Run focused backend and frontend tests for cups listing and fix regressions in the files covered by this feature
- [ ] T017 Run `composer cs`, `composer stan`, frontend typecheck/tests, and `git diff --check`; record any unrelated pre-existing failures in the completion report
- [ ] T018 Verify all acceptance scenarios, checklist status, route list, and task completion against `specs/028-cups-spa-listing/spec.md`, `plan.md`, and `quickstart.md`

## Implementation Strategy

1. Deliver US1 first: public API plus SPA listing is the MVP.
2. Add auth visibility policy and service columns as an independent increment.
3. Retire only listing-only legacy code after API and SPA tests pass.
4. Finish with focused then final quality gates.
