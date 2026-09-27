# Tasks: Cup Event Context Badges

## Dependencies

API context read → SPA client/shared badge → four consumers → verification.

## Phase 1: Setup

- [ ] T001 Verify current event-table consumers and legacy stage route in `resources/spa/pages/` and `app/Bridge/Laravel/Provider/WebRoutesServiceProvider.php`

## Phase 2: Foundational API context

- [ ] T002 Add compact context DTO, query DTO, command and list service in `app/Application/{Dto,Service}/CupEvent/`
- [ ] T003 Add `cup-events` batch-list action and public route in `app/Bridge/Laravel/Http/Controllers/Api/V1/Cup/` and `app/Bridge/Laravel/Provider/ApiV1RoutesServiceProvider.php`
- [ ] T004 Extend `CupEventRepository` and `EloquentCupEventRepository` with active cup context lookup in `app/Domain/Cup/CupEvent/` and `app/Infrastructure/Laravel/Eloquent/CupEvent/`
- [ ] T005 Add request coverage for validation, active filtering, compact payload and multiple associations in `tests/Feature/Api/V1/Cup/ListCupEventContextsActionTest.php`

## Phase 3: User Story 1 — Display context (P1)

- [ ] T006 [US1] Add cup context types/client and API tests in `resources/spa/api/{types.ts,cups.ts,cups.test.ts}`
- [ ] T007 [US1] Create reusable linked icon/popover component and its test in `resources/spa/components/{CupEventBadges.vue,CupEventBadges.test.ts}`
- [ ] T008 [US1] Load and render contexts on the competition event table in `resources/spa/pages/competitions/{CompetitionDetailsPage.vue,CompetitionDetailsPage.test.ts}`
- [ ] T009 [US1] Load and render contexts on the person event table in `resources/spa/pages/persons/{PersonViewPage.vue,PersonViewPage.test.ts}`
- [ ] T010 [US1] Load and render contexts on the rank event table in `resources/spa/pages/persons/{PersonRanksPage.vue,PersonRanksPage.test.ts}`
- [ ] T011 [US1] Load and render contexts on the group event table in `resources/spa/pages/groups/{GroupDetailsPage.vue,GroupDetailsPage.test.ts}`

## Phase 4: Polish

- [ ] T012 Add Belarusian user-facing strings in `resources/lang/by.json`
- [ ] T013 Run targeted API and SPA tests, then `composer cs`, `composer stan`, `composer rector`, `composer test`, `npm run ci`, and `git diff --check`
